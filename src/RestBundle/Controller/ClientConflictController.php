<?php

namespace RestBundle\Controller;

use DataBundle\Entity\ClientBan;
use DataBundle\Entity\ConflictReasonManage;
use DataBundle\Entity\Gender;
use DataBundle\Entity\RateManage;
use DataBundle\Entity\RateManageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\Route("/conflictive")
 */
class ClientConflictController extends Controller
{
    /**
     * @Rest\Get("/client/{dni}")
     */
    public function getConflictiveClientReasonsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $em = $this->get('doctrine.orm.entity_manager');
        $reasons = $layer->getComplexResult($em->getRepository('DataBundle:ClientBan')
            ->createQueryBuilder("cb")
            ->select('cp.id, cp.name')
            ->where('cb.client = :client')
            ->join('cb.ban', 'cp')
            ->andWhere('cp.id = cb.ban')
            ->setParameter("client", $em->getRepository("DataBundle:Client")->find($request->get('dni'))));
        return $this->get('response')->success("", $reasons);
    }

    /**
     * @Rest\Post("/client/{dni}")
     */
    public function postConflictiveClientReasonsAction(Request $request)
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
        if (!$permission->hasPermission("SET_USER_CONFLICTIVE", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Validate path param user. Remove conflictivities first. */
        $conflictivities = $layer->getMultiResults('DataBundle:ClientBan', ['client' => $request->get('dni')]);
        if (null !== $conflictivities) {
            foreach ($conflictivities as $conflictivity) {
                $em->remove($conflictivity);
            }
        }
        $em->flush();


        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $conflictivitiesInput = $layer->deserialize($body, "array<DataBundle\Entity\ClientBanType>");
        if (null === $conflictivitiesInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $client = $layer->getSingleResult('DataBundle:Client', [
            "dni" => $request->get('dni')
        ]);
        if (null === $client) {
            return $this->get('response')->error(400, "USER_NOT_FOUND");
        }
        if (null !== $conflictivitiesInput && is_array($conflictivitiesInput)) {
            foreach ($conflictivitiesInput as $newConflictivity) {
                $newConflictivity = $layer->getSingleResult('DataBundle:ClientBanType', [
                    "id" => $newConflictivity->getId()
                ]);
                if (null !== $newConflictivity) {
                    $clientReason = new ClientBan();
                    $clientReason->setClient($client);
                    $clientReason->setBan($newConflictivity);
                    $em->persist($clientReason);
                }
            }
        }
        $layer->wsPush(["client" => $client, "conflictivies" => $conflictivitiesInput], 'api_clientsbans');
        $em->flush();
        return $this->get('response')->success("BANS_UPDATED");
    }

    /**
     * @Rest\Post("/clients/table")
     */
    public function clientsConflictiveTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:Client";
        $mainOrder = array("column" => "dni", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

}
