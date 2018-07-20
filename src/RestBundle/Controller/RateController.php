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

class RateController extends Controller
{

    /**
     * @Rest\Post("/table/rates")
     */
    public function clientsRatesCrudTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientEntrancePricing";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/rate")
     */
    public function postRateAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_ROOM_RATES", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $rateInput = $layer->deserialize($body, "DataBundle\Entity\ClientEntrancePricing");
        if (null === $rateInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $rateDB = $layer->getSingleResult('DataBundle:ClientEntrancePricing', [
            "id" => $rateInput->getId()
        ]);
        if (null === $rateDB) {
            return $this->get('response')->error(400, "RATE_NOT_FOUND");
        }
        $rateDB->setTransEs($rateInput->getTransEs());
        $rateDB->setTransEn($rateInput->getTransEn());
        $rateDB->setPrice($rateInput->getPrice());
        $em->flush();

        $rateManage = new RateManage();
        $rateManage->setRate($rateDB);
        $rateManage->setUser($layer->getSessionUser());
        $rateManage->setType($layer->getSingleResult('DataBundle:RateManageType', ["name" => "EDIT"]));
        $em->persist($rateManage);
        $em->flush();
        $layer->wsPush($rateManage,'api_rates');
        return $this->get('response')->success("RATE_UPDATED");
    }

    /**
     * @Rest\Put("/rate")
     */
    public function putRateAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_ROOM_RATES", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $rateInput = $layer->deserialize($body, "DataBundle\Entity\ClientEntrancePricing");
        if (null === $rateInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $rateDB = $layer->getSingleResult('DataBundle:ClientEntrancePricing', [
            "id" => $rateInput->getId()
        ]);

        if (null !== $rateDB) {
            return $this->get('response')->error(400, "RATE_ALREADY_ONDB");
        }
        $em->persist($rateInput);
        $em->flush();

        $rateManage = new RateManage();
        $rateManage->setRate($rateInput);
        $rateManage->setUser($layer->getSessionUser());
        $rateManage->setType($layer->getSingleResult('DataBundle:RateManageType', ["name" => "ADD"]));
        $em->persist($rateManage);
        $em->flush();
        $layer->wsPush($rateManage,'api_rates');
        return $this->get('response')->success("RATE_CREATED");
    }

    /**
     * @Rest\Delete("/rate/{id}")
     */
    public function delRateAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_ROOM_RATES", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $rateInput = $layer->deserialize($body, "DataBundle\Entity\ClientEntrancePricing");
        if (null === $rateInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $rateDB = $layer->getSingleResult('DataBundle:ClientEntrancePricing', [
            "id" => $rateInput->getId()
        ]);
        
        if (null === $rateDB) {
            return $this->get('response')->error(400, "RATE_NOT_FOUND");
        }

        $rateManage = new RateManage();
        $rateManage->setRate($rateDB);
        $rateManage->setUser($layer->getSessionUser());
        $rateManage->setType($layer->getSingleResult('DataBundle:RateManageType', ["name" => "DELETE"]));
        $em->persist($rateManage);
        $layer->wsPush($rateManage,'api_rates');
        $em->remove($rateDB);
        $em->flush();
        return $this->get('response')->success("CONFLICTREASON_DELETED");
    }

}
