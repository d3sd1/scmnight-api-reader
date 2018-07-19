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

class StockController extends Controller
{
    /**
     * @Rest\Post("/table")
     */
    public function stockCrudTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("RouteLoader");
        $selectData = "DataBundle:StockItem";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

}
