<?php

namespace RestBundle\Controller;

use DataBundle\Entity\ConfigType;
use RestBundle\Utils\Random;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use DataBundle\Entity\ConfigManage;

/**
 * @Rest\Route("/config")
 */
class ConfigController extends Controller
{
    /**
     * @Rest\Post("/logo")
     */
    public function postLogoAction(Request $request)
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
        if (!$permission->hasPermission("CHANGE_LOGO", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = json_decode($request->getContent(), true);
        } catch (\Exception $e) {
            return $this->get('response')->error(400, "INVALID_LOGO_IMAGE");
        }
        if (!array_key_exists('img', $body)) {
            return $this->get('response')->error(400, "INVALID_LOGO_IMAGE");
        }
        $img = $body['img'];

        /* Validar imagen en B64 y, de paso, obtener su info */
        try {
            $imgMetadata = getimagesize($img);
            $imgBSize = (int)(strlen(rtrim($img, '=')) * 3 / 4);
            $imgKbSize = $imgBSize / 1024;
            $imgMbSize = $imgKbSize / 1024;
        } catch (\Exception $e) {
            return $this->get('response')->error(400, "INVALID_LOGO_IMAGE");
        }

        /*
         * Check image MB size. Max allowed: 10.
         */
        if ($imgMbSize > 10) {
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
        if (!in_array($myme, $validMymes)) {
            return $this->get('response')->error(400, "INVALID_LOGO_FORMATIMAGE");
        }

        $logo = $layer->getSingleResult('DataBundle:ExtraConfig', [
            "config" => "base64_logo"
        ]);
        try {
            $logo->setValue($img);
            $em->flush();
        } catch (\Exception $e) {
            return $this->get('response')->error(400, "INVALID_LOGO_MBSIZEIMAGE");
        }
        $layer->wsPush($img, 'api_logo');

        return $this->get('response')->success("LOGO_IMAGE_CHANGED");
    }

    /**
     * @Rest\Get("/find/{name}")
     */
    public function findConfigAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $config = $layer->getSingleResult('DataBundle:Config', [
            "config" => $request->get("name")
        ]);
        return $this->get('response')->success("", $config);
    }

    /**
     * @Rest\Post("/table")
     */
    public function allConfigsAction(Request $request)
    {
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
    public function editConfigAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_CONFIG", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $configInput = $layer->deserialize($body, "DataBundle\Entity\Config");
        if (null === $configInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $confDB = $layer->getSingleResult('DataBundle:Config', [
            "config" => $configInput->getConfig()
        ]);

        if (null === $confDB) {
            return $this->get('response')->error(400, "NO_CONFIG_FOUND");
        }
        $dataType = $layer->getSingleResult('DataBundle:ConfigType', [
            "type" => $confDB->getDataType()->getType()
        ]);
        switch ($dataType->getType()) {
            case "boolean":
                $newValue = boolval($configInput->getValue());
                break;
            case "double":
                $newValue = floatval($configInput->getValue());
                break;
            case "int":
                $newValue = intval($configInput->getValue());
                break;
            case "string":
                $newValue = $configInput->getValue();
                break;
            default:
                $newValue = $configInput->getValue();
        }
        $confDB->setValue($newValue);
        $configLog = new ConfigManage();
        $configLog->setTargetConfig($confDB);
        $configLog->setUser($this->get('security.token_storage')->getToken()->getUser());
        $em->persist($configLog);
        $em->flush();
        $layer->wsPush($configLog, 'api_config_manage');

        return $this->get('response')->success("CONFIG_UPDATED", $confDB);
    }

}
