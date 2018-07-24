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
 * @Rest\Route("/chat")
 */
class ChatController extends Controller
{

    /**
     * @Rest\Get("/users")
     */
    public function getChatUsers(Request $request)
    {
        $layer = $this->get('rest.layer');
        $em = $this->get('doctrine.orm.entity_manager');
        $permission = $this->get("permissions");
        $response = $this->get('response');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("USE_CHAT", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        $users = $layer->getComplexResult(
            $em->getRepository("DataBundle:User")
                ->createQueryBuilder("u")
                ->select("u")
                ->join('DataBundle:Permission', 'p', 'WITH', 'p.action = \'USE_CHAT\'')
                ->join('DataBundle:UserPermissions', 'up', 'WITH', 'up.user = u.id AND up.permission = p.id')
                ->where("u.chatStatus IS NOT NULL")
        );

        return $this->get('response')->success("", $users);
    }

    /**
     * @Rest\Post("/user/inactive")
     */
    public function setChatUserInactive(Request $request)
    {

        $layer = $this->get('rest.layer');
        $em = $this->get('doctrine.orm.entity_manager');
        $permission = $this->get("permissions");
        $response = $this->get('response');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("USE_CHAT", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        $user->setChatStatus($layer->getSingleResult('DataBundle:UserChatStatus', ['chatStatus' => 'IDLE']));
        $em->flush();
        $layer->wsPush($user,'chat_users');

        return $response->success("", $user);
    }

    /**
     * @Rest\Post("/user/active")
     */
    public function setChatUserActive(Request $request)
    {

        $layer = $this->get('rest.layer');
        $em = $this->get('doctrine.orm.entity_manager');
        $permission = $this->get("permissions");
        $response = $this->get('response');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        /* Check permissions */
        if (!$permission->hasPermission("USE_CHAT", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        $user->setChatStatus($layer->getSingleResult('DataBundle:UserChatStatus', ['chatStatus' => 'ONLINE']));
        $em->flush();
        $layer->wsPush($user,'chat_users');

        return $response->success("", $user);
    }

    /**
     * @Rest\Post("/user/offline")
     */
    public function setChatOfflineActive(Request $request)
    {
        $layer = $this->get('rest.layer');
        $em = $this->get('doctrine.orm.entity_manager');
        $permission = $this->get("permissions");
        $response = $this->get('response');

        /* Check actual user */
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "");
        }

        /* Check permissions */
        if (!$permission->hasPermission("USE_CHAT", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        $user->setChatStatus($layer->getSingleResult('DataBundle:UserChatStatus', ['chatStatus' => 'OFFLINE']));
        $em->flush();
        $layer->wsPush($user,'chat_users');

        return $response->success("", $user);
    }
}
