<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use RestBundle\Entity\AuthToken;
use DataBundle\Entity\UserLogin;

class SessionController extends Controller {

    /**
     * @Rest\Post("/auth-login")
     */
    public function postAuthTokensAction(Request $request) {
        try {
            $data = $this->container->get('jms_serializer')->deserialize($request->getContent(), "RestBundle\Entity\LoginData", "json");
        }
        catch (\JMS\Serializer\Exception\RuntimeException $e) {
            return $this->get('response')->error(500, "NO_LOGIN_PROVIDED");
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('DataBundle:User')->findOneByDni($data->getUser()->getDni());
        if (!$user) {
            return $this->get('response')->error(400, "INCORRECT_LOGIN");
        }
        $isPasswordValid = $this->get('security.password_encoder')->isPasswordValid($user, $data->getUser()->getPassword());

        if (!$isPasswordValid) {
            return $this->get('response')->error(400, "INCORRECT_LOGIN");
        }

        if ($request->request->get("extended_session") === true) {
            $sessionLength = $this->getParameter('web_extended_token_ttl');
        }
        else {
            $sessionLength = $this->getParameter('web_default_token_ttl');
        }
        $authToken = new AuthToken();
        $authToken->setExtendedSession($data->getExtendedsession());
        $authToken->setValue($this->get('lexik_jwt_authentication.encoder')
                        ->encode([
                            'username' => $user->getUsername(),
                            'exp' => time() + $sessionLength
                        ])
        );
        $authToken->setCreatedDate(new \DateTime('now'));
        $authToken->setUser($user);

        /* Push logout to users */
        try {
            $usersToLogout = $em->getRepository('RestBundle:AuthToken')
                    ->createQueryBuilder("at")
                    ->select('at.value as token')
                    ->where('at.user = :user')
                    ->andWhere('at.createdDate > :cdate')
                    ->setParameter("user", $user)
                    ->setParameter("cdate", (new \DateTime())->modify('-1 day'))
                    ->getQuery()
                    ->getResult();
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push(['logoutUsers' => $usersToLogout], 'auth_guard');
        }
        catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }

        /* Delete old auth tokens */
        $em->getRepository('RestBundle:AuthToken')
                ->createQueryBuilder('at')
                ->delete()
                ->where('at.user = :user')
                ->andWhere('at.createdDate < :cdate')
                ->setParameter("user", $user)
                ->setParameter("cdate", (new \DateTime())->modify('-1 day'))
                ->getQuery()
                ->getResult();

        /* Delete account recover codes */
        $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $user)
                ->getQuery()
                ->getResult();

        /* Insert token */
        $em->persist($authToken);

        /* Insert log */
        $loginLog = new UserLogin();
        $loginLog->setUser($user);
        $coords = $request->request->get("coords");
        if (null !== $coords) {
            $loginLog->setLat($coords["lat"]);
            $loginLog->setLng($coords["lng"]);
        }
        $em->persist($loginLog);
        $em->flush();

        return $this->get('response')->success("", $authToken);
    }

    /**
     * @Rest\Delete("/auth-logout/{id}")
     */
    public function removeAuthTokenAction(Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $authToken = $em->getRepository('RestBundle:AuthToken')->find($request->get('id'));
        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();

        if ($authToken && $authToken->getUser()->getId() === $connectedUser->getId()) {
            $this->get('security.token_storage')->setToken(null);
            $em->remove($authToken);
            $em->flush();
            return $this->get('response')->success("SESSION_CLOSE_SUCCESS");
        }
        else {
            return $this->get('response')->error(403, "USER_HAS_NO_PERMISSIONS");
        }
    }

}
