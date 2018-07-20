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
 * @Rest\Route("/entrances")
 */
class EntranceController extends Controller
{


    /**
     * @Rest\Get("/all")
     */
    public function roomClientsEntrancesWsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $entrances = $layer->getAllResults('DataBundle:ClientEntrance');
        return $this->get('response')->success("", $entrances);
    }

    /**
     * @Rest\Post("/table/all")
     */
    public function totalclientsAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientEntrance";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/table/room")
     */
    public function roomclientsAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientRoom";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Get("/room")
     */
    public function roomclientsWsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $entrances = $layer->getAllResults('DataBundle:ClientRoom');
        return $this->get('response')->success("", $entrances);
    }

}
