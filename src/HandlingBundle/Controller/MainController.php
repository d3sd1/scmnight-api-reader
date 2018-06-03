<?php
namespace HandlingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    public function errorAction(Request $request)
    {
        $response = new Response("ERROR");
        return $response;
    }
    public function defaultPageAction(Request $request)
    {
        $response = new Response("WELCOME TO API REST");
        return $response;
    }
}
