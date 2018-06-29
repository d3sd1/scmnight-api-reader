<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use DataBundle\Entity\UserManage;
use RestBundle\Utils\Random;

class UserController extends Controller
{


    /**
     * @Rest\Get("/entrances")
     */
    public function roomUsersWsAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:UserEntrance')
            ->findAll();
        return $this->get('response')->success("", $users);
    }

    /**
     * @Rest\Post("/table/all")
     */
    public function allUsersAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:User";
        $mainOrder = array("column" => "id", "dir" => "ASC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/table/room")
     */
    public function roomUsersAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:UserRoom";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/table/historical")
     */
    public function historicalUsersAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:UserEntrance";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Put("/add")
     */
    public function addusersAction(Request $request)
    {
        $user = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\User", "json");

        $em = $this->getDoctrine()->getManager();

        $userOnDb = count($em->getRepository('DataBundle:User')->findBy(array("dni" => $user->getDni()))) > 0;

        if ($userOnDb) {
            return $this->get('response')->error(500, "USER_ALREADY_ON_DB");
        }

        $random = new Random();
        $newPassword = $random->randomAlphaNumeric(rand(8, 30));
        $this->get('encoder')->setUserPassword($user, $newPassword);
        $message = (new \Swift_Message())
            ->setSubject('Bienvenido a SCM')
            ->setFrom(array('admin@scmnight.com' => $em->getRepository('DataBundle:Config')->loadConfig("disco_name")))
            ->setTo($user->getEmail())
            ->setBody($this->renderView('EmailBundle::newuser.html.twig', array('password' => $newPassword)));
        $this->get('mailer')->send($message);
        $em->persist($user);

        $userManage = new UserManage();
        $userManage->setTargetUser($user);
        $userManage->setUser($this->get('security.token_storage')->getToken()->getUser());
        $userManage->setType($em->getRepository('DataBundle:UserManageType')->findOneBy(array("name" => "ADD")));
        $em->persist($userManage);
        $em->flush();
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($userManage, "json"), 'api_users_manage');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }

        return $this->get('response')->success("USER_ADD_SUCCESS", $this->container->get('jms_serializer')->serialize($user, "json"));
    }

    /**
     * @Rest\Post("/mod")
     */
    public function editusersAction(Request $request)
    {
        try {
            $user = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\User", "json");
            $em = $this->getDoctrine()->getManager();

            $userOnDb = $em->getRepository('DataBundle:User')->findOneBy(array("dni" => $user->getDni()));
            //TODO: comprobar permiso para editar users

            if (null == $userOnDb || null == $userOnDb->getId()) {
                return $this->get('response')->error(400, "USER_NOT_FOUND");
            }
            $userOnDb->setEmail($user->getEmail());
            $userOnDb->setFirstname($user->getFirstname());
            $userOnDb->setLastname($user->getLastname());
            $userOnDb->setDni($user->getDni());
            $userOnDb->setTelephone($user->getTelephone());
            $userOnDb->setAddress($user->getAddress());
            $em->flush();

            $userManage = new UserManage();
            $userManage->setTargetUser($userOnDb);
            $userManage->setUser($this->get('security.token_storage')->getToken()->getUser());
            $userManage->setType($em->getRepository('DataBundle:UserManageType')->findOneBy(array("name" => "EDIT")));
            $em->persist($userManage);
            $em->flush();
            try {
                $pusher = $this->container->get('websockets.pusher');
                $pusher->push($this->container->get('jms_serializer')->serialize($userManage, "json"), 'api_users_manage');
            } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
                $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
            }

            return $this->get('response')->success("USER_EDIT_SUCCESS");
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
        }
    }

    /**
     * @Rest\Delete("/delete/{dni}")
     */
    public function removeusersAction(Request $request)
    {
        try {
            $em = $this->getDoctrine()->getManager();

            $actualUser = $this->get('security.token_storage')->getToken()->getUser();
            $delUser = $em->getRepository('DataBundle:User')
                ->createQueryBuilder("u")
                ->select('*')
                ->select('u')
                ->where('u.dni = :dni')
                ->setParameter('dni', $request->get('dni'))
                ->getQuery()
                ->getSingleResult();
            //TODO: comprobar permiso para borrar users
            $userManage = new UserManage();
            $userManage->setTargetUser($delUser);
            $userManage->setUser($this->get('security.token_storage')->getToken()->getUser());
            $userManage->setType($em->getRepository('DataBundle:UserManageType')->findOneBy(array("name" => "DELETE")));
            $em->persist($userManage);
            $em->flush();
            try {
                $pusher = $this->container->get('websockets.pusher');
                $pusher->push($this->container->get('jms_serializer')->serialize($userManage, "json"), 'api_users_manage');
            } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
                $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
            }
            $em->remove($delUser);
            $em->flush();
            return $this->get('response')->success("USER_DEL_SUCCESS");
        } catch (\Doctrine\ORM\NoResultException $e) {
            return $this->get('response')->error(400, "USER_NOT_FOUND");
        }
    }

}
