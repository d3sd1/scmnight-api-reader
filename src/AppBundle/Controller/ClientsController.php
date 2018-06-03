<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\Encoding;
use DataBundle\Entity\Client;
use DataBundle\Entity\ClientEntrance;
use \DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;


class ClientsController extends Controller
{

    /**
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Put("/client/entrance")
     */
    public function clientEntranceAction(Request $request)
    {
        /* Proceder al parseo de datos y validación de errores */
        $data = json_decode($request->getContent(), true);
        if ($data == "" || $data == null)
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Incomplete parameters");
        }
        $forceAccess = filter_var(@$data["forceAccess"], FILTER_VALIDATE_BOOLEAN);
        $isVipAccess = filter_var(@$data["vip"], FILTER_VALIDATE_BOOLEAN);
        $certificate = @$data["cert"];
        $utils = new Encoding();
        $cert = $utils->decodeDniCertificate($certificate);
        if ($cert == null || $cert == "")
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate");
        }
        $personData = $utils->parseDniCertificate($cert);
        if ($personData == null || count($personData) == 0)
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate (No data)");
        }

        $em = $this->get('doctrine.orm.entity_manager');
        /* Añadir persona a la base de datos */
        $person = $em->getRepository('DataBundle:Client')->find($personData["nif"]);

        if ($person === null)
        {
            $person = new Client();
            $person->setConflictive(false);
        }

        $person->setDni($personData["nif"]);
        $person->setName($personData["name"]);
        $person->setSurname1($personData["surname1"]);
        $person->setSurname2($personData["surname2"]);
        $person->setBirthDate($personData["birthdate"]);
        $person->setSerialNumber($personData["serialNumber"]);
        $person->setNationality($personData["nationality"]);
        $person->setBiometric($personData["biometric"]);
        $em->persist($person);
        $em->flush();

        /* Manejar entrada */
        $type = $em->getRepository('DataBundle:ClientEntrance')->getEntranceType($person->getDni());
        $conflictive = $em->getRepository('DataBundle:Client')->isConflictive($person->getDni());
        $room_actual = $em->getRepository('DataBundle:ClientRoom')->createQueryBuilder('pa')->select('count(pa.client)')->getQuery()->getSingleScalarResult();
        $room_max = $em->getRepository('DataBundle:Config')->loadConfig("maxPersonsInRoom");

        $entrance = new ClientEntrance();
        $entrance->setClient($person);
        $entrance->setVip($isVipAccess);

        if ($forceAccess)
        {
            if ($type == "JOIN")
            {
                $entrance->setType("FORCED_ACCESS");
                $em->getRepository('DataBundle:ClientRoom')->add($entrance);
            }
            else
            {
                $entrance->setType("LEAVE");
                $em->getRepository('DataBundle:ClientRoom')->rm($entrance);
            }
        }
        else
        {
            switch ($type)
            {
                case "JOIN":
                    if ($conflictive)
                    {
                        $entrance->setType("DENIED_CONFLICTIVE");
                        $this->apiErrorMsg = "User is conflictive.";
                        $success = false;
                    }
                    else if ($room_actual >= $room_max)
                    {
                        $entrance->setType("DENIED_FULL");
                        $this->apiErrorMsg = "Room is full.";
                        $success = false;
                    }
                    else
                    {
                        $entrance->setType($type);
                        $em->getRepository('DataBundle:ClientRoom')->add($entrance);
                    }
                    break;
                default:
                        $entrance->setType($type);
                        $em->getRepository('DataBundle:ClientRoom')->rm($entrance);
                    break;
            }
        }

        try
        {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($entrance, "json"), 'api_clients');
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
