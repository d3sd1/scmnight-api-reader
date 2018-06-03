<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\Encoding;
use DataBundle\Entity\User;
use DataBundle\Entity\UserEntrance;
use \DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;


class UsersController extends Controller
{

    /**
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Put("/user/entrance")
     */
    public function usersEntranceAction(Request $request)
    {
        /* Proceder al parseo de datos y validación de errores */
        $data = json_decode($request->getContent(), true);
        if ($data == "" || $data == null)
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Incomplete parameters");
        }
        $certificate = @$data["cert"];
        $utils = new Encoding();
        $cert = $utils->decodeDniCertificate($certificate);
        if ($cert == null || $cert == "")
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate");
        }
        $userData = $utils->parseDniCertificate($cert);
        if ($userData == null || count($userData) == 0)
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate (No data)");
        }

        $em = $this->get('doctrine.orm.entity_manager');
        /* Añadir persona a la base de datos */
        $user = $em->getRepository('DataBundle:User')->findOneBy(array('dni' => $userData["nif"]));

        if ($user === null)
        {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException("User not allowed");
        }

        /* Manejar entrada */
        $type = $em->getRepository('DataBundle:UserEntrance')->getEntranceType($user);
        
        $entrance = new UserEntrance();
        $entrance->setUser($user);

        switch ($type)
        {
            case "JOIN":
                $entrance->setType($type);
                $em->getRepository('DataBundle:UserRoom')->add($entrance);
                break;
            default:
                $entrance->setType($type);
                $em->getRepository('DataBundle:UserRoom')->rm($entrance);
                break;
        }

        try
        {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($entrance, "json"), 'api_users');
        }
        catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e)
        {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $em->persist($entrance);
        $em->flush();

        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($entrance, "json"));
        return $response;
    }

}
