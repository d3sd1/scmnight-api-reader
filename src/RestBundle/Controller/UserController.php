<?php

namespace RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/user")
 */
class UserController extends Controller
{

    /**
     * Get profile info. Needed for session.
     * @Rest\Get("/info")
     */
    public function getUserinfoAction()
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $user = $layer->getSessionUser();
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }
        return $response->success("", $user);
    }

    /**
     * Update actual user profile info.
     * @Rest\Post("/info")
     */
    public function postUserinfoAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_PROFILE", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $userInput = $layer->deserialize($body, "DataBundle\Entity\User");
        if (null === $userInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        /* Validate user password */
        if (!$layer->validateSessionUserPassword($userInput->getPassword())) {
            return $this->get('response')->error(400, "PASSWORD_DOESNT_MATCH");
        }

        /* Set new password */
        if ($userInput->getNewpass() != null && $userInput->getNewpass() != "") {
            $this->get('encoder')->setUserPassword($user, $userInput->getNewpass());
        }

        /* Update on database. Info has not to be pushed with WS since only the actual user will see it. */
        $user->setEmail($userInput->getEmail());
        $user->setAddress($userInput->getAddress());
        $user->setTelephone($userInput->getTelephone());
        $user->setLangCode($userInput->getLangCode());

        $em->flush();
        return $this->get('response')->success("PROFILE_EDIT_SUCCESS");
    }

    /**
     * Get actual user permissions. Needed for session.
     * @Rest\Get("/permissions")
     */
    public function getUserpermissionsAction()
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permission = $this->get("permissions");
        $em = $this->get('doctrine.orm.entity_manager');

        /* Check actual user */
        $user = $layer->getSingleResult('DataBundle:User', array('id' => "_SESSION_USER_INFO"));
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }

        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $permissions = $layer->getComplexResult(
            $em->getRepository('DataBundle:UserPermissions')
                ->createQueryBuilder("up")
                ->select("p")
                ->where("up.user = :user")
                ->join("DataBundle:Permission", "p")
                ->andWhere("p.id = up.permission")
                ->setParameter("user", $layer->getSessionUser())
        );
        if (null === $permissions) {
            return $response->error(400, "SESSION_USER_HAS_NO_PERMISSIONS");
        }
        return $response->success("", $permissions);
    }

    /**
     * @Rest\Post("/all/table")
     */
    public function totalUsersAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:User";
        $mainOrder = array("column" => "id", "dir" => "ASC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }
}
