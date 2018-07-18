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
    public function sessionDiscoInfoAction(Request $request)
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

    /**
     * @Rest\View()
     * @Rest\Get("/translates/{langKey}")
     */
    public function sessionTranslatesAction(Request $request)
    {
        $config = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:CustomTranslate')
            ->findBy(["langKey" => $request->get("langKey")]);
        return $this->get('response')->success("", $config);
    }

    /**
     * @Rest\View()
     * @Rest\Post("/translates")
     */
    public function sessionTranslateChangeAction(Request $request)
    {
        $customTranslate = $this->container->get('jms_serializer')->deserialize($request->getContent(), "DataBundle\Entity\CustomTranslate", "json");
        $em = $this->get('doctrine.orm.entity_manager');
        $customTranslateDB = $em->getRepository('DataBundle:CustomTranslate')->findOneBy([
            "key" => $customTranslate->getKey(),
            "langKey" => $em->getRepository('DataBundle:CustomTranslateAvailableLangs')->findOneBy(["langKey" => $customTranslate->getLangKey()->getLangKey()])
        ]);
        if(null === $customTranslateDB)
        {
            return $this->get('response')->error(400, "LANG_KEY_NOT_FOUND");
        }
        $customTranslateDB->setValue($customTranslate->getValue());
        $em->flush();
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->container->get('jms_serializer')->serialize($customTranslate, "json"), 'api_translations');
        } catch (\Gos\Component\WebSocketClient\Exception\BadResponseException $e) {
            $this->get('sendlog')->warning('Could not push logged out data to websockets due to offline server.');
        }
        return $this->get('response')->success("LANG_KEY_UPDATED", $customTranslate);
    }

    /**
     * @Rest\Post("/table/translates")
     */
    public function clientsConflictReasonsCrudTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:CustomTranslate";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/translatesavailable")
     */
    public function sessionTranslatesAvailableAction(Request $request)
    {
        $config = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:CustomTranslateAvailableLangs')
            ->findAll();
        return $this->get('response')->success("", $config);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/translatedefault")
     */
    public function sessionTranslateDefaultAction(Request $request)
    {
        $config = $this->get('doctrine.orm.entity_manager')
            ->getRepository('DataBundle:CustomTranslate')
            ->findBy(["langKey" => $request->get("langKey")]);
        return $this->get('response')->success("", $config);
    }
}