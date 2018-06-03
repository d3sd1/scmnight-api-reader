<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

class ClientController extends Controller
{

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
        return $this->get('response')->success("",$data);
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
        return $this->get('response')->success("",$data);
    }

}
