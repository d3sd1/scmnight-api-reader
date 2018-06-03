<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use DataBundle\Entity\ConfigManage;

class ConfigController extends Controller {

    /**
     * @Rest\Post("/table/all")
     */
    public function allConfigsAction(Request $request) {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:Config";
        $mainOrder = array("column" => "config", "dir" => "ASC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Post("/mod")
     */
    public function editConfigAction(Request $request) {
        $conf = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\Config", "json");
        $em = $this->getDoctrine()->getManager();
        $confDB = $em->getRepository('DataBundle:Config')->findOneBy(array("config" => $conf->getConfig()));
        if (null === $confDB) {
            return $this->get('response')->error(400, "NO_CONFIG_FOUND");
        }
        switch ($confDB->getDataType()) {
            case "boolean":
                $newValue = boolval($conf->getValue());
                break;
            case "double":
                $newValue = floatval($conf->getValue());
                break;
            case "int":
                $newValue = intval($conf->getValue());
                break;
            case "string":
                $newValue = $conf->getValue();
                break;
        }
        $confDB->setValue($newValue);

        $configLog = new ConfigManage();
        $configLog->setTargetConfig($confDB);
        $configLog->setUser($this->get('security.token_storage')->getToken()->getUser());
        $em->persist($configLog);
        $em->flush();
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($configLog, "json"), 'api_config_manage');
        }
        catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }

        return $this->get('response')->success("", $confDB);
    }

}
