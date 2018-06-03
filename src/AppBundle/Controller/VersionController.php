<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;

class VersionController extends Controller
{

    /**
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Get("/version")
     */
    public function versionAction(Request $request)
    {
        $version = $this->get('doctrine.orm.entity_manager')->getRepository('DataBundle:ScmConfig')->loadConfig("version");
        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize(array("version" => $version), "json"));
        return $response;
    }

}
