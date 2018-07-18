<?php

namespace RestBundle\Controller;

use DataBundle\Entity\ConfigType;
use RestBundle\Utils\Random;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use DataBundle\Entity\ConfigManage;

class ConfigController extends Controller {
    /**
     * @Rest\Post("/logo")
     */
    public function postLogoAction(Request $request) {
        /*
        Check if user sent good data
        */
        try{
            $body = json_decode($request->getContent(),true);
        }
        catch(\Exception $e)
        {
            return $this->get('response')->error(400, "INVALID_LOGO_IMAGE");
        }
        if(!array_key_exists('img',$body))
        {
            return $this->get('response')->error(400, "INVALID_LOGO_IMAGE");
        }

        $img = $body['img'];
        /* Validar imagen en B64 y, de paso, obtener su info */
        try{
            $imgMetadata = getimagesize($img);
            $imgBSize = (int) (strlen(rtrim($img, '=')) * 3 / 4);
            $imgKbSize    = $imgBSize / 1024;
            $imgMbSize    = $imgKbSize / 1024;
        }
        catch(\Exception $e)
        {
            return $this->get('response')->error(400, "INVALID_LOGO_IMAGE");
        }

        /*
         * Check image MB size. Max allowed: 10.
         */
        if($imgMbSize > 10)
        {
            return $this->get('response')->error(400, "INVALID_LOGO_MBSIZEIMAGE");
        }
        /*
        Check if it's on valid format. Accepted: SVG, PNG 'n JPG/JPEG
        */
        $myme = $imgMetadata["mime"];
        $validMymes = array(
            "image/png",
            "image/x-citrix-png	",
            "image/x-png	",
            "image/svg+xml",
            "image/jpeg",
            "image/x-citrix-jpeg",
            "image/pjpeg"
        );
        if(!in_array($myme, $validMymes))
        {
            return $this->get('response')->error(400, "INVALID_LOGO_FORMATIMAGE");
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $logo = $em->getRepository('DataBundle:ExtraConfig')->findOneBy(["config" => "base64_logo"]);
        $logo->setValue($img);
        $em->flush();
        //TODO: avisar por WS.
        return $this->get('response')->success("LOGO_IMAGE_CHANGED");
    }

    /**
     * @Rest\Get("/find/{name}")
     */
    public function findConfigAction(Request $request)
    {
        $config = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:Config')
            ->findOneBy(["config" => $request->get("name")]);
        return $this->get('response')->success("", $config);
    }

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
        $dataType = $em->getRepository('DataBundle:ConfigType')->findOneBy(array("id" => $confDB->getDataType()));
        switch ($dataType->getType()) {
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
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }

        return $this->get('response')->success("CONFIG_UPDATED", $confDB);
    }

}
