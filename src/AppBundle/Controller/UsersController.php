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
     * @Rest\View()
     * @Rest\Put("/user/entrance")
     */
    public function usersEntranceAction(Request $request)
    {
        /* Proceder al parseo de datos y validación de errores */
        $data = json_decode($request->getContent(), true);
        if ($data == "" || $data == null) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Incomplete parameters");
        }
        $certificate = @$data["cert"];
        $utils = new Encoding();
        $cert = $utils->decodeDniCertificate($certificate);
        if ($cert == null || $cert == "") {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate");
        }
        $userData = $utils->parseDniCertificate($cert);
        if ($userData == null || count($userData) == 0) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate (No data)");
        }

        $em = $this->get('doctrine.orm.entity_manager');
        /* Añadir persona a la base de datos */
        $user = $em->getRepository('DataBundle:User')->findOneBy(array('dni' => $userData["dni"]));

        if (null === $user) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException("User not allowed");
        }
        /* Manejar entrada */
        $type = $em->getRepository('DataBundle:UserEntrance')->getEntranceType($user);

        $entrance = new UserEntrance();
        $entrance->setUser($user);

        switch ($type->getName()) {
            case "JOIN":
                $entrance->setType($type);
                $em->getRepository('DataBundle:UserRoom')->add($entrance);
                break;
            default:
                $entrance->setType($type);
                $em->getRepository('DataBundle:UserRoom')->rm($entrance);
        }
        $em->persist($entrance);
        $em->flush();
        $entranceJson = $this->container->get('jms_serializer')->serialize($entrance, "json");
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($entranceJson, 'api_userentrances');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $response = new JsonResponse();
        $response->setContent($entranceJson);
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Put("/user/entrance/{dni}")
     */
    public function usersEntranceByDniAction(Request $request)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        /* Añadir persona a la base de datos */
        $user = $em->getRepository('DataBundle:User')->findOneBy(array('dni' => $request->get("dni")));

        if (null === $user) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException("User not allowed");
        }
        /* Manejar entrada */
        $type = $em->getRepository('DataBundle:UserEntrance')->getEntranceType($user);

        $entrance = new UserEntrance();
        $entrance->setUser($user);

        switch ($type->getName()) {
            case "JOIN":
                $entrance->setType($type);
                $em->getRepository('DataBundle:UserRoom')->add($entrance);
                break;
            default:
                $entrance->setType($type);
                $em->getRepository('DataBundle:UserRoom')->rm($entrance);
        }
        $entranceJson = $this->container->get('jms_serializer')->serialize($entrance, "json");
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($entranceJson, 'api_userentrances');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $em->persist($entrance);
        $em->flush();
        $response = new JsonResponse();
        $response->setContent($entranceJson);
        return $response;
    }

}
