<?php

namespace RestBundle\Controller;

use DataBundle\Entity\UserRecover;
use RestBundle\Utils\Random;
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
     * Login user into system.
     * @Rest\Post("/login")
     */
    public function postAuthTokensAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
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
        $usersToLogout = $layer->getComplexResult(
            $em->getRepository('RestBundle:AuthToken')
                ->createQueryBuilder("at")
                ->select('at.value as token')
                ->where('at.user = :user')
                ->andWhere('at.createdDate > :cdate')
                ->setParameter("user", $userDB)
                ->setParameter("cdate", (new \DateTime())->modify('-1 day'))
        );
        $layer->wsPush(['logoutUsers' => $usersToLogout], 'auth_guard');

        /* Delete old auth tokens */
        $layer->getComplexResult(
            $em->getRepository('RestBundle:AuthToken')
                ->createQueryBuilder('at')
                ->delete()
                ->where('at.user = :user')
                ->andWhere('at.createdDate < :cdate')
                ->setParameter("user", $userDB)
                ->setParameter("cdate", (new \DateTime())->modify('-1 day'))
        );

        /* Delete account recover codes */
        $layer->getComplexResult(
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $userDB)
        );

        /* Insert token */
        $em->persist($authToken);

        /* Insert log */
        $lat = array_key_exists("lat", $userInput->getCoords()) ? $userInput->getCoords()["lat"] : "0";
        $lng = array_key_exists("lat", $userInput->getCoords()) ? $userInput->getCoords()["lng"] : "0";
        $loginLog = new UserLogin();
        $loginLog->setUser($userDB);
        $loginLog->setLat($lat);
        $loginLog->setLng($lng);
        $em->persist($loginLog);

        /* Set user chat connected */
        $userDB->setChatStatus($layer->getSingleResult('DataBundle:UserChatStatus', array('chatStatus' => 'ONLINE')));
        $layer->wsPush($userDB, "chat_users");
        $em->flush();
        return $this->get('response')->success("LOGIN_SUCCESS", $authToken);
    }

    /**
     * Logout user from system.
     * @Rest\Delete("/logout/{id}")
     */
    public function removeAuthTokenAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check actual user */
        $user = $layer->getSingleResult('DataBundle:User', array('id' => "_SESSION_USER_INFO"));
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        $authToken = $layer->getSingleResult('RestBundle:AuthToken', array('id' => $request->get('id')));
        if ($authToken && $authToken->getUser()->getId() === $layer->getSessionUser()->getId()) {
            $this->get('security.token_storage')->setToken(null);
            $em->remove($authToken);

            /* Set user disconnected of chat */
            $user->setChatStatus($layer->getSingleResult('DataBundle:UserChatStatus', array('chatStatus' => 'OFFLINE')));
            $layer->wsPush($user, "chat_users");
            $em->flush();

            return $this->get('response')->success("SESSION_CLOSE_SUCCESS");
        } else {
            return $this->get('response')->error(403, "USER_HAS_NO_PERMISSIONS");
        }
    }


    /**
     * Recover password step 1: send code
     * @Rest\Post("/recover")
     */
    public function recoverAccountAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');


        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $userInput = $layer->deserialize($body, "DataBundle\Entity\User");
        if (null === $userInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        /* Check user */
        $user = $layer->getSingleResult('DataBundle:User', array('dni' => $userInput->getDni()));
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("RECOVER_PASSWORD", $user)) {
            return $response->error(400, "RECOVER_NO_PERMISSIONS");
        }

        /* Check there are no petitions on last 30 mins */
        $prevPetitions = $layer->getComplexResult(
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->where('ur.user = :user')
                ->setMaxResults(1)
                ->orderBy('ur.dateExpires', 'DESC')
                ->setParameter("user", $user)
        );
        if(array_key_exists(0, $prevPetitions)) {
            $prevPetition = $prevPetitions[0];
        }
        else {
            $prevPetition = null;
        }
        if (null !== $prevPetition && !($prevPetition->getDateExpires() < (new \DateTime()))) {
            return $response->error(400, "RECOVER_WAIT_FOR_RESEND");
        }

        /* Generar código aleatorio */
        $random = new Random();
        $recoverCode = $random->randomAlphaNumeric(20);

        /* Delete account recover codes */
        $layer->getComplexResult(
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $user)
        );

        /* Insertar código con fecha de expiración a la base de datos */
        $configSecondsToExpire = $layer->getSingleResult('DataBundle:Config', array("config" => "recover_code_seconds_expire"));
        if (null === $configSecondsToExpire) {
            return $response->error(400, "SERVER_NOT_CONFIGURED");
        }
        $secondsToExpire = $configSecondsToExpire->getValue();
        $expireDate = (new \DateTime("now"))->modify('+' . $secondsToExpire . ' seconds');

        $code = new UserRecover();
        $code->setCode($recoverCode);
        $code->setDateExpires($expireDate);
        $code->setUser($user);
        $em->persist($code);
        $em->flush();

        /* Enviar correo de recuperación a la cuenta, en el idioma del usuario */
        $extraData = array();
        $extraData["code"] = $recoverCode;
        $extraData["text_recover_pass_title"] = $layer->getTranslate("EMAIL.RECOVERPASS.TITLE", $user->getLangCode());
        $extraData["text_recover_pass_content"] = $layer->getTranslate("EMAIL.RECOVERPASS.CONTENT", $user->getLangCode());
        $extraData["copyright_powered"] = $layer->getTranslate("EMAIL.POWEREDBY", $user->getLangCode());
        $this->get('mail')->send($user->getEmail(), $layer->getTranslate("EMAIL.RECOVERPASS.TITLE", $user->getLangCode()), "recover_account", $extraData, $extraData["text_recover_pass_content"] . " " . $recoverCode);

        return $this->get('response')->success("RECOVER_EMAIL_SENT");
    }

    /**
     * Recover password step 2: use code
     * @Rest\Post("/recover/code")
     */
    public function recoverAccountCodeAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $inputCode = $layer->deserialize($body, "DataBundle\Entity\UserRecover");
        if (null === $inputCode) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        /* Check actual code */
        $recoverCode = $layer->getSingleResult('DataBundle:UserRecover', ["code" => $inputCode->getCode()]);
        if (null === $recoverCode) {
            return $response->error(400, "RECOVER_CODE_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("RECOVER_PASSWORD", $recoverCode->getUser())) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check code expiration */
        $user = $layer->getSingleResult('DataBundle:User', ['dni' => $recoverCode->getUser()->getDni()]);
        if ($recoverCode->getDateExpires() <= (new \DateTime("now"))) {
            /* Delete account recover codes */
            $layer->getComplexResult(
                $em->getRepository('DataBundle:UserRecover')
                    ->createQueryBuilder('ur')
                    ->delete()
                    ->where('ur.user = :user')
                    ->setParameter("user", $user)
            );
            $this->get('response')->error(400, "RECOVER_CODE_EXPIRED");
        }

        /* Reset user password */
        $newPass = (new Random())->randomAlphaNumeric(rand(8, 30));
        $this->get('encoder')->setUserPassword($user, $newPass);
        /* Delete account recover codes */
        $layer->getComplexResult(
            $em->getRepository('DataBundle:UserRecover')
                ->createQueryBuilder('ur')
                ->delete()
                ->where('ur.user = :user')
                ->setParameter("user", $user)
        );
        $em->flush();

        /* SEND EMAIL */

        /* Enviar correo de recuperación a la cuenta, en el idioma del usuario */
        $extraData = array();
        $extraData["newpass"] = $newPass;
        $extraData["text_recover_newpass_title"] = $layer->getTranslate("EMAIL.RECOVERPASS.SENDNEW.TITLE", $user->getLangCode());
        $extraData["text_recover_newpass_content"] = $layer->getTranslate("EMAIL.RECOVERPASS.SENDNEW.CONTENT", $user->getLangCode());
        $extraData["copyright_powered"] = $layer->getTranslate("EMAIL.POWEREDBY", $user->getLangCode());

        $this->get('mail')->send($user->getEmail(), $extraData["text_recover_newpass_title"], "recover_account_newpass", $extraData, $extraData["text_recover_newpass_content"] . " " . $newPass);

        return $this->get('response')->success("RECOVER_ACCOUNT_SUCCESS");
    }

}
