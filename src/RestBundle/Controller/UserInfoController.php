<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;

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

}
