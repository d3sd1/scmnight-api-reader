<?php

namespace RestBundle\Controller;

use DataBundle\Entity\PermissionListManage;
use DataBundle\Entity\PermissionsLists;
use DataBundle\Entity\UserPermissions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\Route("/crud/permission")
 */
class PermissionsListController extends Controller
{

    /**
     * @Rest\Get()
     */
    public function getPermissionsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $reasons = $layer->getAllResults('DataBundle:Permission');
        return $this->get('response')->success("", $reasons);
    }

    /**
     * @Rest\Get("/list/{id}")
     */
    public function getPermissionListAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $listPermissions = $layer->getMultiResults('DataBundle:PermissionsLists', ["idList" => $request->get("id")]);
        return $this->get('response')->success("", $listPermissions);
    }


    /**
     * @Rest\Get("/lists")
     */
    public function getPermissionListsAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $listPermissions = $layer->getAllResults('DataBundle:PermissionList');
        return $this->get('response')->success("", $listPermissions);
    }

    /**
     * @Rest\Get("/user/{id}")
     */
    public function getPermissionsUserAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $response = $this->get('response');
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $layer->getSingleResult("DataBundle:User", [
            "id" => $request->get('id')
        ]);
        if (null === $user) {
            return $response->error(400, "USER_NOT_FOUND");
        }
        $userPermissionsOnEntity = $layer->getComplexResult($em->getRepository('DataBundle:UserPermissions')
            ->createQueryBuilder("up")
            ->select('up')
            ->where('up.user = :user')
            ->setParameter("user", $layer->getSingleResult("DataBundle:User", [
                "id" => $request->get('id')
            ])));
        $userPermissions = [];
        foreach($userPermissionsOnEntity as $userPermission) {
            $userPermissions[] = $userPermission->getPermission();
        }
        return $response->success("", $userPermissions);
    }
    /**
     * @Rest\Post("/user/relation/{id}")
     */
    public function postPermissionsUserAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_PERMISSIONS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $permissionsLists = $layer->deserialize($body, "array<DataBundle\Entity\Permission>");
        if (null === $permissionsLists || !is_array($permissionsLists)) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }


        $primaryUserDb = $layer->getSingleResult('DataBundle:User', ["id" => $request->get("id")]);

        /* Clear previous permissions :) */
        $em->getRepository('DataBundle:UserPermissions')
            ->createQueryBuilder('pl')
            ->delete()
            ->where('pl.user = :user')
            ->setParameter("user", $primaryUserDb->getId())->getQuery()->getResult();

        foreach ($permissionsLists as $permission) {

            $permissionDB = $layer->getSingleResult('DataBundle:Permission', ["id" => $permission->getId()]);
            if (null === $permissionDB) {
                return $this->get('response')->error(400, "PERMISSION_NOT_FOUND");
            }

            $duplicateEntity = $layer->getSingleResult('DataBundle:UserPermissions', [
                "user" => $primaryUserDb->getId(),
                "permission" => $permissionDB->getId()
            ]);
            if(null !== $duplicateEntity) {
                return $this->get('response')->error(400, "INRTERNAL_DUPLICITY_ERROR");
            }

            $relation = new UserPermissions();
            $relation->setPermission($permissionDB);
            $relation->setUser($primaryUserDb);
            $em->persist($relation);
            $em->flush();
        }
        /* tiempo real despues xd
        $manage = new PermissionListManage();
        $manage->setPermissionList($input);
        $manage->setUser($layer->getSessionUser());
        $manage->setType($layer->getSingleResult('DataBundle:PermissionListManageType', ["name" => "ADD"]));
        $em->persist($manage);
        $em->flush();
        $layer->wsPush($manage, 'api_manage_permissions');
        $em->flush();
    }
    /* First: Clear actual permissions for the list */

        return $this->get('response')->success("PERMISSIONLIST_CREATED");
    }

    /**
     * @Rest\Post("/list/relation/{id}")
     */
    public function postPermissionsListsAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_PERMISSIONS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $permissionsLists = $layer->deserialize($body, "array<DataBundle\Entity\PermissionsLists>");
        if (null === $permissionsLists || !is_array($permissionsLists)) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }


        /* Clear previous permissions :) */
        $primaryListDb = $layer->getSingleResult('DataBundle:PermissionList', ["listKeyName" => $permissionsLists[0]->getIdList()->getListKeyName()]);
        $num = $em->getRepository('DataBundle:PermissionsLists')
            ->createQueryBuilder('pl')
            ->delete()
            ->where('pl.idList = :lis')
            ->setParameter("lis", $primaryListDb->getId())->getQuery()->getResult();

        foreach ($permissionsLists as $permissionLists) {

            $permissionListDB = $layer->getSingleResult('DataBundle:PermissionList', ["listKeyName" => $permissionLists->getIdList()->getListKeyName()]);
            if (null === $permissionListDB) {
                return $this->get('response')->error(400, "PERMISSIONLIST_NOT_FOUND");
            }
            $permissionDB = $layer->getSingleResult('DataBundle:Permission', ["id" => $permissionLists->getIdPermission()->getId()]);
            if (null === $permissionListDB) {
                return $this->get('response')->error(400, "PERMISSION_NOT_FOUND");
            }
            /* List missmatch */
            if($permissionListDB->getId() != $primaryListDb->getId()) {
                return $this->get('response')->error(400, "PERMISSIONLIST_MISSMATCH");
            }
            $duplicateEntity = $layer->getSingleResult('DataBundle:PermissionsLists', [
                "idList" => $permissionListDB->getId(),
                "idPermission" => $permissionDB->getId()
            ]);
            if(null !== $duplicateEntity) {
                return $this->get('response')->error(400, "INRTERNAL_DUPLICITY_ERROR");
            }

            $relation = new PermissionsLists();
            $relation->setIdPermission($permissionDB);
            $relation->setIdList($permissionListDB);
            $em->persist($relation);
            $em->flush();
        }
            /* tiempo real despues xd
            $manage = new PermissionListManage();
            $manage->setPermissionList($input);
            $manage->setUser($layer->getSessionUser());
            $manage->setType($layer->getSingleResult('DataBundle:PermissionListManageType', ["name" => "ADD"]));
            $em->persist($manage);
            $em->flush();
            $layer->wsPush($manage, 'api_manage_permissions');
            $em->flush();
        }
        /* First: Clear actual permissions for the list */

        return $this->get('response')->success("PERMISSIONLIST_CREATED");
    }


    /**
     * @Rest\Put("/list")
     */
    public function putPermissionListAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_PERMISSIONS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $input = $layer->deserialize($body, "DataBundle\Entity\PermissionList");
        if (null === $input) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $inputDB = $layer->getSingleResult('DataBundle:PermissionList', ["listKeyName" => $input->getListKeyName()]);
        if (null !== $inputDB) {
            return $this->get('response')->error(400, "PERMISSIONLIST_ALREADY_ONDB");
        }
        $em->persist($input);
        $em->flush();

        $manage = new PermissionListManage();
        $manage->setPermissionList($input);
        $manage->setUser($layer->getSessionUser());
        $manage->setType($layer->getSingleResult('DataBundle:PermissionListManageType', ["name" => "ADD"]));
        $em->persist($manage);
        $em->flush();
        $layer->wsPush($manage, 'api_manage_permissions');
        $em->flush();
        return $this->get('response')->success("PERMISSIONLIST_CREATED");
    }

    /**
     * @Rest\Delete("/list/{id}")
     */
    public function delPermissionListAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_PERMISSIONS", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        $inputDb = $layer->getSingleResult('DataBundle:PermissionList', ["id" => $request->get("id")]);
        if (null === $inputDb) {
            return $this->get('response')->error(400, "PERMISSIONLIST_NOT_FOUND");
        }

        $manage = new PermissionListManage();
        $manage->setPermissionList($inputDb);
        $manage->setUser($layer->getSessionUser());
        $manage->setType($layer->getSingleResult('DataBundle:PermissionListManageType', ["name" => "DELETE"]));
        $em->persist($manage);
        $layer->wsPush($manage, 'api_manage_permissions');
        $em->remove($inputDb);
        $em->flush();
        return $this->get('response')->success("PERMISSIONLIST_DELETED");
    }


    /**
     * @Rest\Post("/list/table")
     */
    public function tablePermissionListAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("Tables");
        $selectData = "DataBundle:PermissionList";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }
}
