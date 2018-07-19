<?php

namespace RestBundle\Controller;

use DataBundle\Entity\ClientBan;
use DataBundle\Entity\ConflictReasonManage;
use DataBundle\Entity\Gender;
use DataBundle\Entity\RateManage;
use DataBundle\Entity\RateManageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\Route("/crud/clients/conflictive")
 */
class ClientConflictiveController extends Controller
{

    /**
     * @Rest\Post("/")
     */
    public function postClientConflictiveAction(Request $request)
    {
        $conflictReason = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\ClientBanType", "json");
        $em = $this->get('doctrine.orm.entity_manager');

        $conflictReasonDB = $em->getRepository('DataBundle:ClientBanType')->find($conflictReason->getId());
        if (null === $conflictReasonDB) {
            return $this->get('response')->error(400, "CONFLICTREASON_NOT_FOUND");
        }
        $conflictReasonDB->setTransEs($conflictReason->getTransEs());
        $conflictReasonDB->setTransEn($conflictReason->getTransEn());
        $em->flush();

        $conflictReasonManage = new ConflictReasonManage();
        $conflictReasonManage->setConflictReason($conflictReasonDB);
        $conflictReasonManage->setUser($this->get('security.token_storage')->getToken()->getUser());
        $conflictReasonManage->setType($em->getRepository('DataBundle:ConflictReasonManageType')->findOneBy(array("name" => "EDIT")));
        $em->persist($conflictReasonManage);
        $em->flush();
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($conflictReasonManage, "json"), 'api_conflictreasons');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_UPDATED");
    }

    /**
     * @Rest\Put("/")
     */
    public function putClientConflictiveAction(Request $request)
    {
        $conflictReason = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\ClientBanType", "json");
        $em = $this->get('doctrine.orm.entity_manager');
        $conflictReasonDB = $em->getRepository('DataBundle:ClientBanType')->findOneBy(["name" => $conflictReason->getName()]);
        if (null !== $conflictReasonDB) {
            return $this->get('response')->error(400, "CONFLICTREASON_ALREADY_ONDB");
        }
        $em->persist($conflictReason);
        $em->flush();

        $conflictReasonManage = new ConflictReasonManage();
        $conflictReasonManage->setConflictReason($conflictReason);
        $conflictReasonManage->setUser($this->get('security.token_storage')->getToken()->getUser());
        $conflictReasonManage->setType($em->getRepository('DataBundle:ConflictReasonManageType')->findOneBy(array("name" => "ADD")));
        $em->persist($conflictReasonManage);
        $em->flush();
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($conflictReasonManage, "json"), 'api_conflictreasons');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_CREATED");
    }

    /**
     * @Rest\Delete("/{id}")
     */
    public function delClientConflictiveAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $conflictReasonDB = $em->getRepository('DataBundle:ClientBanType')->findOneBy(["id" => $request->get('id')]);
        if (null === $conflictReasonDB) {
            return $this->get('response')->error(400, "CONFLICTREASON_NOT_FOUND");
        }

        $conflictReasonManage = new ConflictReasonManage();
        $conflictReasonManage->setConflictReason($conflictReasonDB);
        $conflictReasonManage->setUser($this->get('security.token_storage')->getToken()->getUser());
        $conflictReasonManage->setType($em->getRepository('DataBundle:ConflictReasonManageType')->findOneBy(array("name" => "DELETE")));
        $em->persist($conflictReasonManage);
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($conflictReasonManage, "json"), 'api_conflictreasons');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $em->remove($conflictReasonDB);
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_DELETED");
    }


    /**
     * @Rest\Post("/table")
     */
    public function tableClientConflictiveAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientBanType";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }
}
