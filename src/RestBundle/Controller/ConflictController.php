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
 * @Rest\Route("/crud/conflictive/reasons")
 */
class ConflictController extends Controller
{

    /**
     * @Rest\Get("/")
     */
    public function getConflictsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $reasons = $layer->getAllResults('DataBundle:ClientBanType');
        return $this->get('response')->success("", $reasons);
    }


    /**
     * @Rest\Post("/")
     */
    public function postConflictAction(Request $request)
    {

        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("MANAGE_ROOM_CONFLICTS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $conflictInput = $layer->deserialize($body, "DataBundle\Entity\ClientBanType");
        if (null === $conflictInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $conflictReasonDB = $layer->getSingleResult('DataBundle:ClientBanType', [
            "id" => $conflictInput->getId()
        ]);
        if (null === $conflictReasonDB) {
            return $this->get('response')->error(400, "CONFLICTREASON_NOT_FOUND");
        }
        $conflictReasonDB->setTransEs($conflictInput->getTransEs());
        $conflictReasonDB->setTransEn($conflictInput->getTransEn());
        $em->flush();

        $conflictReasonManage = new ConflictReasonManage();
        $conflictReasonManage->setConflictReason($conflictReasonDB);
        $conflictReasonManage->setUser($layer->getSessionUser());
        $conflictReasonManage->setType($layer->getSingleResult('DataBundle:ConflictReasonManageType', ["name" => "EDIT"]));
        $em->persist($conflictReasonManage);
        $em->flush();
        $layer->wsPush($conflictReasonManage,'api_conflictreasons');
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_UPDATED");
    }

    /**
     * @Rest\Put("/")
     */
    public function putConflictAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("MANAGE_ROOM_CONFLICTS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $conflictInput = $layer->deserialize($body, "DataBundle\Entity\ClientBanType");
        if (null === $conflictInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $conflictReasonDB = $layer->getSingleResult('DataBundle:ClientBanType', ["name" => $conflictInput->getName()]);
        if (null !== $conflictReasonDB) {
            return $this->get('response')->error(400, "CONFLICTREASON_ALREADY_ONDB");
        }
        $em->persist($conflictInput);
        $em->flush();

        $conflictReasonManage = new ConflictReasonManage();
        $conflictReasonManage->setConflictReason($conflictInput);
        $conflictReasonManage->setUser($layer->getSessionUser());
        $conflictReasonManage->setType($layer->getSingleResult('DataBundle:ConflictReasonManageType', ["name" => "ADD"]));
        $em->persist($conflictReasonManage);
        $em->flush();
        $layer->wsPush($conflictReasonManage,'api_conflictreasons');
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_CREATED");
    }

    /**
     * @Rest\Delete("/{id}")
     */
    public function delConflictAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("MANAGE_ROOM_CONFLICTS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $conflictInput = $layer->deserialize($body, "DataBundle\Entity\ClientBanType");
        if (null === $conflictInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $conflictReasonDB = $layer->getSingleResult('DataBundle:ClientBanType', ["name" => $conflictInput->getName()]);
        if (null === $conflictReasonDB) {
            return $this->get('response')->error(400, "CONFLICTREASON_NOT_FOUND");
        }

        $conflictReasonManage = new ConflictReasonManage();
        $conflictReasonManage->setConflictReason($conflictReasonDB);
        $conflictReasonManage->setUser($layer->getSessionUser());
        $conflictReasonManage->setType($layer->getSingleResult('DataBundle:ConflictReasonManageType', ["name" => "DELETE"]));
        $em->persist($conflictReasonManage);
        $layer->wsPush($conflictReasonManage,'api_conflictreasons');
        $em->remove($conflictReasonDB);
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_DELETED");
    }


    /**
     * @Rest\Post("/table")
     */
    public function tableConflictAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientBanType";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }
}
