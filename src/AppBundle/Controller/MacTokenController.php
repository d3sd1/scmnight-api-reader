<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\AppToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\View\View;

class MacTokenController extends Controller
{

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Get("/mac-token")
     */
    public function macTokenAction(Request $request)
    {

        $mac = $request->headers->get("mac");
        if ($mac != null && $mac != "")
        {
            $em = $this->get('doctrine.orm.entity_manager');

            $computer = $em->getRepository('AppBundle:Computer')
                    ->findOneBy(array('mac' => $mac));
            // Bad user
            if (!$computer || !$computer->getActive())
            {
                return View::create(null, 403);
            }
            else
            {
                $appToken = new AppToken();
                $appToken->setValue($this->get('lexik_jwt_authentication.encoder')
                                ->encode([
                                    'mac' => $computer->getMac(),
                                    'exp' => time() + $this->getParameter('app_token_ttl')
                                ])
                );
                $appToken->setCreatedDate(new \DateTime('now'));
                $appToken->setComputer($computer);

                /* Delete old auth tokens */
                $em->getRepository('AppBundle:AppToken')
                        ->createQueryBuilder('at')
                        ->delete()
                        ->where('at.computer = :computer')
                        ->setParameter("computer", $computer)
                        ->getQuery()
                        ->getResult();

                /* Insert token */
                $em->persist($appToken);
                $em->flush();
            }
        }
        else
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException("No MAC given");
        }

        $response = new JsonResponse();
        $response->setContent($this->container->get('jms_serializer')->serialize($appToken, "json"));
        return $response;
    }

}
