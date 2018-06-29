<?php

namespace RestBundle\Controller;

use DataBundle\Entity\ClientBan;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

class ClientController extends Controller
{

    /**
     * @Rest\Get("/conflictive_reasons")
     */
    public function getConflictiveReasonsAction(Request $request)
    {
        $reasons = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:ClientBanType')
            ->findAll();
        return $this->get('response')->success("", $reasons);
    }

    /**
     * @Rest\Get("/conflictive/{dni}")
     */
    public function getConflictiveClientReasonsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $reasons = $em->getRepository('DataBundle:ClientBan')
            ->createQueryBuilder("cb")
            ->select('cp.id, cp.name')
            ->where('cb.client = :client')
            ->join('cb.ban', 'cp')
            ->andWhere('cp.id = cb.ban')
            ->setParameter("client", $em->getRepository("DataBundle:Client")->find($request->get('dni')))
            ->getQuery()
            ->getResult();
        return $this->get('response')->success("", $reasons);
    }

    /**
     * @Rest\Post("/conflictive/{dni}")
     */
    public function postConflictiveClientReasonsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $conflictivities = $em->getRepository('DataBundle:ClientBan')->findBy(array('client' => $request->get('dni')));
        if (null !== $conflictivities) {
            foreach ($conflictivities as $conflictivity) {
                $em->remove($conflictivity);
            }
        }
        $em->flush();
        $newConflictivities = $this->container->get('jms_serializer')->deserialize($request->getContent(), "array<DataBundle\Entity\ClientBanType>", "json");
        $client = $em->getRepository('DataBundle:Client')->find($request->get('dni'));
        if (null === $client) {
            return $this->get('response')->error(400, "USER_NOT_FOUND");
        }
        if (null !== $newConflictivities && is_array($newConflictivities)) {
            foreach ($newConflictivities as $newConflictivity) {
                $newConflictivity = $em->getRepository('DataBundle:ClientBanType')->find($newConflictivity->getId());
                if (null !== $newConflictivity) {
                    $clientReason = new ClientBan();
                    $clientReason->setClient($client);
                    $clientReason->setBan($newConflictivity);
                    $em->persist($clientReason);
                }
            }
        }
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize(["client" => $client, "conflictivies" => $newConflictivities], "json"), 'api_clientsbans');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        $em->flush();
        return $this->get('response')->success("BANS_UPDATED");
    }

    /**
     * @Rest\Post("/table/conflictive")
     */
    public function clientsTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:Client";
        $mainOrder = array("column" => "dni", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\Get("/entrances")
     */
    public function roomClientsEntrancesWsAction(Request $request)
    {
        $clients = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:ClientEntrance')
            ->findAll();
        return $this->get('response')->success("", $clients);
    }

    /**
     * @Rest\Post("/table/all")
     */
    public function totalclientsAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientEntrance";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("",$data);
    }

    /**
     * @Rest\Post("/table/room")
     */
    public function roomclientsAction(Request $request)
    {

        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:ClientRoom";
        $mainOrder = array("column" => "date", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("",$data);
    }

    /**
     * @Rest\Get("/room")
     */
    public function roomclientsWsAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:ClientRoom')
            ->findAll();
        return $this->get('response')->success("", $users);
    }

}
