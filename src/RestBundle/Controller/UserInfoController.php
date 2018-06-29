<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

class UserInfoController extends Controller
{

    /**
     * @Rest\Get("/userinfo")
     */
    public function getUserinfoAction()
    {
        $loggedUserId = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:User')
            ->findOneBy(array('id' => $loggedUserId));
        return $this->get('response')->success("",$user);
    }

    /**
     * @Rest\Post("/userinfo")
     */
    public function postUserinfoAction(Request $request)
    {
        $userNewInfo = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\User", "json");
        $loggedUserId = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:User')
            ->findOneBy(array('id' => $loggedUserId));

        $encoder = $this->get('encoder.factory')->getEncoder($user);

        if (!$encoder->isPasswordValid($user->getPassword(), $userNewInfo->getPassword(), $user->getSalt())) {
            return $this->get('response')->error(400, "PASSWORD_DOESNT_MATCH");
        }
        if ($userNewInfo->getNewpass() != null && $userNewInfo->getNewpass() != "") {
            $this->get('encoder')->setUserPassword($user, $userNewInfo->getNewpass());
        }

        $user->setEmail($userNewInfo->getEmail());
        $user->setAddress($userNewInfo->getAddress());
        $user->setTelephone($userNewInfo->getTelephone());
        $user->setLangCode($userNewInfo->getLangCode());

        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->get('response')->success("PROFILE_EDIT_SUCCESS");
    }

    /**
     * @Rest\Get("/userpermissions")
     */
    public function getUserpermissionsAction()
    {
        $loggedUser = $this->container->get('security.token_storage')->getToken()->getUser();
        $permissions = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:UserPermissions')
            ->createQueryBuilder("up")
            ->select("p")
            ->where("up.user = :user")
            ->join("DataBundle:Permission", "p")
            ->andWhere("p.id = up.permission")
            ->setParameter("user", $loggedUser)
            ->getQuery()->getResult();
        return $this->get('response')->success("", $permissions);
    }

}
