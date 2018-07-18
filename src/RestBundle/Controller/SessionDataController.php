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

class SessionDataController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/discoinfo")
     */
    public function clientInfoGendersAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /*
         * Manual data given here. Keep it manual.
         */
        $data = array(
            "logo" => $em->getRepository('DataBundle:ExtraConfig')->findOneBy(["config" => "base64_logo"])->getValue(),
            "discoName" => $em->getRepository('DataBundle:Config')->findOneBy(["config" => "disco_name"])->getValue()
        );
        return $this->get('response')->success("", $data);
    }
}
