<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use RestBundle\Entity\AuthToken;
use DataBundle\Entity\UserLogin;

/**
 * @Rest\Route("/auth")
 */
class LoginController extends Controller
{

    /**
     * @Rest\Post("/login")
     */
    public function postAuthTokensAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "NO_LOGIN_PROVIDED");
        }
        $userInput = $layer->deserialize($body, "RestBundle\Entity\LoginData");
        if (null === $userInput || null == $userInput->getUser()) {
            return $response->error(400, "NO_LOGIN_PROVIDED");
        }

        /* Validate user */
        $userDB = $layer->getSingleResult('DataBundle:User', array('dni' => $userInput->getUser()->getDni()));
        if (!$userDB) {
            return $this->get('response')->error(400, "INCORRECT_LOGIN");
        }

        /* Validate password */
        if (!$layer->validateUserPassword($userDB, $userInput->getUser()->getPassword())) {
            return $this->get('response')->error(400, "INCORRECT_LOGIN");
        }

        if ($userInput->getExtendedSession() === true) {
            $sessionLength = $this->getParameter('web_extended_token_ttl');
        } else {
            $sessionLength = $this->getParameter('web_default_token_ttl');
        }

        /* Generate user token */
        $authToken = new AuthToken();
        $authToken->setExtendedSession($userInput->getExtendedsession());
        $authToken->setValue($this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'exp' => time() + $sessionLength
            ])
        );
        $authToken->setCreatedDate(new \DateTime('now'));
        $authToken->setUser($userDB);

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
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
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
        if (null !== $coords && null != $coords["lat"] && null != $coords["lng"]) {
            $loginLog->setLat($coords["lat"]);
            $loginLog->setLng($coords["lng"]);
        }
        $em->persist($loginLog);
        $em->flush();
        return $this->get('response')->success("LOGIN_SUCCESS", $authToken);
    }

    /**
     * @Rest\Delete("/logout/{id}")
     */
    public function removeAuthTokenAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $authToken = $em->getRepository('RestBundle:AuthToken')->find($request->get('id'));
        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();

        if ($authToken && $authToken->getUser()->getId() === $connectedUser->getId()) {
            $this->get('security.token_storage')->setToken(null);
            $em->remove($authToken);
            $em->flush();
            return $this->get('response')->success("SESSION_CLOSE_SUCCESS");
        } else {
            return $this->get('response')->error(403, "USER_HAS_NO_PERMISSIONS");
        }
    }


    /**
     * @Rest\Post("/recover")
     */
    public function recoverAccountAction(Request $request)
    {
        $dni = $request->request->get("dni");
        if ($dni == null || $dni == "") {
            return $this->get('response')->error(400, "NO_USER_PROVIDED");
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('DataBundle:User')->findOneByDni($dni);


        if ($user !== null) {
            if ($this->get("permissions")->hasPermission("UNRECOVERABLE_USER", $user)) {
                return $this->get('response')->error(416, "USER_PERMISSIONS_INMUTABLE");
            }
            /* Generar código aleatorio */
            $random = new Random();
            $recoverCode = $random->randomAlphaNumeric(20);

            /* Delete account recover codes */
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $user)
                ->getQuery()
                ->getResult();

            /* Insertar código con fecha de expiración a la base de datos */
            $secondsToExpire = $em->getRepository('DataBundle:Config')->findOneBy(array("config" => "recover_code_seconds_expire"))->getValue();
            $expireDate = (new \DateTime("now"))->modify('+' . $secondsToExpire . ' seconds');
            $code = new UserRecover();
            $code->setCode($recoverCode);
            $code->setDateExpires($expireDate);
            $code->setUser($user);
            $em->persist($code);
            $em->flush();
            $this->get('mail')->send($user->getEmail(), "Recuperación de cuenta", "recover_account", array('code' => $recoverCode));
        }

        return $this->get('response')->informative("RECOVER_EMAIL_SENT");
    }

    /**
     * @Rest\Post("/recover/code")
     */
    public function recoverAccountCodeAction(Request $request)
    {
        $code = $request->request->get("code");
        if ($code == null || $code == "") {
            return $this->get('response')->error(400, "NO_DATA_PROVIDED");
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $recover = $em->getRepository('DataBundle:UserRecover')->findOneByCode($code);

        if (!$recover || $recover === null) {
            return $this->get('response')->error(400, "NO_RECOVER_FOUND");
        } else if ($recover->getDateExpires() <= (new \DateTime("now"))) {
            $user = $em->getRepository('DataBundle:User')->findOneByDni($recover->getUser()->getDni());
            /* Delete account recover codes */
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $user)
                ->getQuery()
                ->getResult();
            $this->get('response')->error(400, "RECOVER_CODE_EXPIRED");
        } else {
            $user = $em->getRepository('DataBundle:User')->findOneByDni($recover->getUser()->getDni());
            /* Generar contraseña aleatoria */
            $newPass = (new Random())->randomAlphaNumeric(rand(8, 30));
            $this->get('encoder')->setUserPassword($user, $newPass);
            /* Delete account recover codes */
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $user)
                ->getQuery()
                ->getResult();

            /* Actualizar contraseña del usuario en la db */
            $em->flush();
            $this->get('mail')->send($user->getEmail(), "Nueva contraseña", "recover_account_newpass", array('newpass' => $newPass));
        }
        return $this->get('response')->success("RECOVER_ACCOUNT_SUCCESS");
    }

}
