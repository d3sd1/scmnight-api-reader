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
 * @Rest\Route("/user/entrances")
 */
class UserEntranceController extends Controller
{


    /**
     * @Rest\Get("/all")
     */
    public function roomUsersEntrancesWsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $entrances = $layer->getAllResults('DataBundle:UserEntrance');
        return $this->get('response')->success("", $entrances);
    }

    /**
     * @Rest\Post("/all/table")
     */
    public function totalUsersAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:UserEntrance";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/room/table")
     */
    public function roomUsersTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:UserRoom";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Get("/room")
     */
    public function roomUsersAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $entrances = $layer->getAllResults('DataBundle:UserRoom');
        return $this->get('response')->success("", $entrances);
    }

}
