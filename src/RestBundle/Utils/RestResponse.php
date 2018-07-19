<?php

namespace RestBundle\Utils;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RestResponse
{
    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    function error(int $code, String $message)
    {
        $response = new JsonResponse();
        $response->setStatusCode($code);
        $response->setContent($this->container->get('jms_serializer')->serialize(array("message" => $message != null ? $message : ""), "json"));
        return $response;
    }

    function success($message = null, $data = null)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setContent($this->container->get('jms_serializer')->serialize(array("message" => $message != null ? $message : "", "data" => $data != null ? $data : ""), "json"));
        return $response;
    }
}
