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
 * @Rest\Route("/clients")
 */
class ClientController extends Controller
{

    /**
     * @Rest\Post("/table")
     */
    public function clientsCrudTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:Client";
        $mainOrder = array("column" => "dni", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/extradata")
     */
    public function editClientExtradataAction(Request $request)
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
        if (!$permission->hasPermission("SET_CLIENT_INFO", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $clientInput = $layer->deserialize($body, "DataBundle\Entity\Client");
        if (null === $clientInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $clientDB = $layer->getSingleResult('DataBundle:Client',[
            "dni" => $clientInput->getDni()
        ]);
        if (null === $clientDB) {
            return $this->get('response')->error(400, "CLIENT_NOT_FOUND");
        }
        $clientDB->setEmail($clientInput->getEmail());
        $clientDB->setAddress($clientInput->getAddress());
        $newGender = $layer->getSingleResult("DataBundle:Gender", ['id' => $clientInput->getGender()->getId()]);

        if(null === $newGender) {
            return $this->get('response')->error(400, "GENDER_NOT_FOUND");
        }
        $clientDB->setGender($newGender);
        $clientInput->getGender()->setName($newGender->getName());
        $em->flush();
        $layer->wsPush($clientInput,'api_clients_extradata');

        return $this->get('response')->success("CLIENT_EXTRADATA_ADD_SUCCESS", $clientInput);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/genders")
     */
    public function clientInfoGendersAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $genders = $layer->getAllResults('DataBundle:Gender');
        if (null === $genders) {
            $genders = [];
        }
        return $this->get('response')->success("", $genders);
    }

}
