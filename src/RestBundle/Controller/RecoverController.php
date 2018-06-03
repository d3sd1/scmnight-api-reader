<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DataBundle\Entity\UserRecover;
use FOS\RestBundle\Controller\Annotations as Rest;
use RestBundle\Utils\Random;

class RecoverController extends Controller {

    /**
     * @Rest\Post("/recover")
     */
    public function recoverAccountAction(Request $request) {
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
            $this->get('mail')->send($user->getEmail(),"Recuperación de cuenta","recover_account", array('code' => $recoverCode));
        }

        return $this->get('response')->informative("RECOVER_EMAIL_SENT");
    }

    /**
     * @Rest\Post("/recover/code")
     */
    public function recoverAccountCodeAction(Request $request) {
        $code = $request->request->get("code");
        if ($code == null || $code == "") {
            return $this->get('response')->error(400, "NO_DATA_PROVIDED");
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $recover = $em->getRepository('DataBundle:UserRecover')->findOneByCode($code);

        if (!$recover || $recover === null) {
            return $this->get('response')->error(400, "NO_RECOVER_FOUND");
        }
        else if ($recover->getDateExpires() <= (new \DateTime("now"))) {
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
        }
        else {
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
            $this->get('mail')->send($user->getEmail(),"Nueva contraseña","recover_account_newpass", array('newpass' => $newPass));
        }
        return $this->get('response')->success("RECOVER_ACCOUNT_SUCCESS");
    }

}
