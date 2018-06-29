<?php

namespace AppBundle\Controller;

use DataBundle\Entity\ClientEntranceType;
use JMS\Serializer\SerializationContext;
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
        $this->get("app.logger")->error($request->getContent());
        $em = $this->get('doctrine.orm.entity_manager');
        /*
         * Deserializar a la entidad Alta.
         */
        $entrance = $this->get("jms_serializer")->deserialize($request->getContent(), 'DataBundle\Entity\ClientEntrance', 'json');
        $validationErrors = $this->get('validator')->validate($entrance);
        if (count($validationErrors) > 0) {
            throw new \JMS\Serializer\Exception\RuntimeException("Could not deserialize entity: " . $validationErrors);
        }
        if (null === $entrance)
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Entrance is null.");
        }


        $entrance->setDate(new \DateTime("now"));

        $em = $this->get('doctrine.orm.entity_manager');
        $client = $em->getRepository('DataBundle:Client')->find($entrance->getClient()->getDni());
        if (null === $client) {
            $client = new Client();
        }
        $client->setDni($entrance->getClient()->getDni());
        $client->setName($entrance->getClient()->getName());
        $client->setSurname1($entrance->getClient()->getSurname1());
        $client->setSurname2($entrance->getClient()->getSurname2());
        $client->setSurname2($entrance->getClient()->getSurname2());
        $client->setBirthdate($entrance->getClient()->getBirthdate());
        $client->setAddress($entrance->getClient()->getAddress());
        $client->setEmail($entrance->getClient()->getEmail());

        $nationality = $em->getRepository('DataBundle:Nationality')->find($entrance->getClient()->getNationality()->getId());
        if (null === $nationality)
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("No nationality found");
        } else {
            /*
             * Para prevenir que cambien el precio mediante man in the middle.
             */
            $client->setNationality($nationality);
        }

        $gender = $em->getRepository('DataBundle:Gender')->find($entrance->getClient()->getGender()->getId());
        if (null === $gender) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("No gender found");
        } else {
            /*
             * Para prevenir que cambien el precio mediante man in the middle.
             */
            $client->setGender($gender);
        }

        $em->persist($client);
        $em->flush();
        $entrance->setClient($client);

        /* Manejar entrada */
        $type = $em->getRepository('DataBundle:ClientEntrance')->getEntranceType($entrance->getClient()->getDni());
        $room_actual = $em->getRepository('DataBundle:ClientRoom')->createQueryBuilder('pa')->select('count(pa.client)')->getQuery()->getSingleScalarResult();
        $room_max = $em->getRepository('DataBundle:Config')->loadConfig("maxPersonsInRoom");
        if ($entrance->getForceaccess()) {
            if ($type->getType()->getName() == "JOIN") {
                $entrance->setType($em->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => "FORCED_ACCESS"]));
                try {
                    $em->getRepository('DataBundle:ClientRoom')->add($client, $entrance->getVip());
                } catch (\Exception $e) {
                    $em->getRepository('DataBundle:ClientRoom')->rm($entrance);
                }
                $rate = $em->getRepository('DataBundle:ClientEntrancePricing')->find($entrance->getRate()->getId());
                if (null === $rate) {
                    throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("No pricing found");
                } else {
                    /*
                     * Para prevenir que cambien el precio mediante man in the middle.
                     */
                    $entrance->setRate($rate);
                }
            } else {
                $entrance->setType($em->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => "LEAVE"]));
                $em->getRepository('DataBundle:ClientRoom')->rm($entrance);
            }
        } else {
            switch ($type->getType()->getName()) {
                case "JOIN":
                    if ($room_actual >= $room_max) {
                        $entrance->setType($em->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => "DENIED_FULL"]));
                    } else {
                        $entrance->setType($em->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => $type->getType()->getName()]));
                        try {
                            $em->getRepository('DataBundle:ClientRoom')->add($client, $entrance->getVip());
                        } catch (\Exception $e) {
                            $em->getRepository('DataBundle:ClientRoom')->rm($entrance);
                        }

                        $rate = $em->getRepository('DataBundle:ClientEntrancePricing')->find($entrance->getRate()->getId());
                        if (null === $rate) {
                            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("No pricing found");
                        } else {
                            /*
                             * Para prevenir que cambien el precio mediante man in the middle.
                             */
                            $entrance->setRate($rate);
                        }
                    }
                    break;
                default:
                    $entrance->setType($em->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => $type->getType()->getName()]));
                    $em->getRepository('DataBundle:ClientRoom')->rm($entrance);
                    break;
            }
        }
        $em->persist($entrance);
        $em->flush();
        $serializedEntrance = $this->container->get('jms_serializer')->serialize($entrance, "json");
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($serializedEntrance, 'api_clientsentrances');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }

        $response = new JsonResponse();
        $response->setContent($serializedEntrance);
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/client/pricing/{dni}")
     */
    public function clientEntrancePricingAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /* Añadir persona a la base de datos */
        $pricings = $em->getRepository('DataBundle:ClientEntrancePricing')->findAll();
        if ($pricings === null) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("No pricings found");
        }

        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($pricings, "json"));
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/client/entrance/type/{dni}")
     */
    public function clientEntranceTypeAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $person = $em->getRepository('DataBundle:Client')->find($request->get("dni"));
        if (null !== $person) {
            $type = $em->getRepository('DataBundle:ClientEntrance')->getEntranceType($person->getDni())->getType();
        } else {
            $type = $em->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => "JOIN"]);
        }
        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($type, "json"));
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/client/info")
     */
    public function clientEntranceInfoAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /* Proceder al parseo de datos y validación de errores */
        $data = json_decode($request->getContent(), true);
        if ($data == "" || $data == null) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Incomplete parameters");
        }
        $forceAccess = filter_var(@$data["forceAccess"], FILTER_VALIDATE_BOOLEAN);
        $isVipAccess = filter_var(@$data["vip"], FILTER_VALIDATE_BOOLEAN);
        $certificate = @$data["cert"];
        $utils = new Encoding();
        $cert = $utils->decodeDniCertificate($certificate);
        if ($cert == null || $cert == "") {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate");
        }
        $personData = $utils->parseDniCertificate($cert);
        if ($personData == null || count($personData) == 0) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid certificate (No data)");
        }
        $client = $em->getRepository('DataBundle:Client')->find($personData["dni"]);
        if (null == $client) {
            $client = new Client();
        }
        $client->setDni($personData["dni"]);
        $client->setSurname1($personData["surname1"]);
        $client->setSurname2($personData["surname2"]);
        $client->setName($personData["name"]);
        $client->setNationality($em->getRepository('DataBundle:Nationality')->findOneBy(["name" => $personData["nationality"]]));
        $client->setBirthdate($personData["birthdate"]);
        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($client, "json"));
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/client/info/{dni}")
     */
    public function clientEntranceInfoByIdAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $client = $em->getRepository('DataBundle:Client')->find($request->get("dni"));
        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($client, "json"));
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/client/genders")
     */
    public function clientEntranceInfoGendersAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $genders = $em->getRepository('DataBundle:Gender')->findAll();
        if (null === $genders) {
            $genders = [];
        }
        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($genders, "json"));
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/client/nationalities")
     */
    public function clientEntranceInfoNationalitiesAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $nationalities = $em->getRepository('DataBundle:Nationality')->findAll();
        if (null === $nationalities) {
            $nationalities = [];
        }
        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($nationalities, "json"));
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/client/bans/{dni}")
     */
    public function clientEntranceBansAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $person = $em->getRepository('DataBundle:Client')->find($request->get("dni"));

        $conflicts = [];
        if (null !== $person) {
            $conflicts = $em->createQueryBuilder()
                ->select("ct")
                ->from("DataBundle:ClientBan", 'cb')
                ->join("DataBundle:ClientBanType", 'ct')
                ->where('cb.client=:dni')
                ->andWhere('cb.ban=ct.id')
                ->setParameter('dni', $request->get("dni"))
                ->getQuery()
                ->getArrayResult();
        }

        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($conflicts, "json"));
        return $response;
    }

}
