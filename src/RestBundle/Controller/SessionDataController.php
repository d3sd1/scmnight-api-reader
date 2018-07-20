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
 * @Rest\Route("/session")
 */
class SessionDataController extends Controller
{
    /**
     * Get disco basic information
     * @Rest\View()
     * @Rest\Get("/discoinfo")
     */
    public function sessionDiscoInfoAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        /*
         * Manual data given here. Keep it manual.
         */
        $data = array(
            "logo" => $layer->getSingleResult('DataBundle:ExtraConfig', ["config" => "base64_logo"])->getValue(),
            "discoName" => $layer->getSingleResult('DataBundle:Config', ["config" => "disco_name"])->getValue()
        );
        return $this->get('response')->success("", $data);
    }

    /**
     * Get all translations for given lang.
     * @Rest\View()
     * @Rest\Get("/translates/{langKey}")
     */
    public function sessionTranslateSingleAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $translates = $layer->getSingleResult('DataBundle:CustomTranslate', ["langKey" => $request->get("langKey")]);
        return $this->get('response')->success("", $translates);
    }

    /**
     * Get all translations for all langs.
     * @Rest\View()
     * @Rest\Get("/translates")
     */
    public function sessionTranslateMultiAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $translates = $layer->getAllResults('DataBundle:CustomTranslate');
        return $this->get('response')->success("", $translates);
    }

    /**
     * Modify a translation.
     * @Rest\View()
     * @Rest\Post("/translate")
     */
    public function sessionTranslateChangeSingleAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_TRANSLATES", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $translateInput = $layer->deserialize($body, "DataBundle\Entity\CustomTranslate");
        if (null === $translateInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        $langKey = $layer->getSingleResult('DataBundle:CustomTranslateAvailableLangs', ["langKey" => $translateInput->getLangKey()->getLangKey()]);

        if (null === $langKey) {
            return $this->get('response')->error(400, "LANG_NOT_FOUND");
        }
        $customTranslateDB = $layer->getSingleResult('DataBundle:CustomTranslate', [
            "keyId" => $translateInput->getKeyId(),
            "langKey" => $langKey
        ]);

        /* Agregar clave dinámicamente, o actualizarla. */
        if (null === $customTranslateDB) {
            $translateInput->setLangKey($langKey);
            $em->persist($translateInput);
        } else {
            $customTranslateDB->setValue($translateInput->getValue());
        }
        $em->flush();
        $layer->wsPush($translateInput, "api_translations");
        return $this->get('response')->success("LANG_KEY_UPDATED", $translateInput);
    }


    /**
     * Modify translates multiple.
     * @Rest\View()
     * @Rest\Post("/translates")
     */
    public function sessionTranslateChangeMultiAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_TRANSLATES", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $allTranslatesInput = $layer->deserialize($body, "array<DataBundle\Entity\CustomTranslate>");
        if (null === $allTranslatesInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        /* Iterate over translations */
        foreach ($allTranslatesInput as $translateInput) {
            $langKey = $layer->getSingleResult('DataBundle:CustomTranslateAvailableLangs', ["langKey" => $translateInput->getLangKey()->getLangKey()]);
            if (null === $langKey) {
                return $this->get('response')->error(400, "LANG_NOT_FOUND");
            }
            $customTranslateDB = $layer->getSingleResult('DataBundle:CustomTranslate', [
                "keyId" => $translateInput->getKeyId(),
                "langKey" => $langKey
            ]);
            /* Agregar clave dinámicamente, o actualizarla */
            if (null === $customTranslateDB) {
                $translateInput->setLangKey($langKey);
                $em->persist($translateInput);
            } else {
                $customTranslateDB->setValue($translateInput->getValue());
            }
            $layer->wsPush($translateInput, "api_translations");
        }
        $em->flush();
        return $this->get('response')->success("", $allTranslatesInput);
    }

    /**
     * Delete translates multiple.
     * @Rest\View()
     * @Rest\Delete("/translates")
     */
    public function sessionTranslateDeleteMultiAction(Request $request)
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
        if (!$permission->hasPermission("MANAGE_TRANSLATES", $user)) {
            return $response->error(400, "NO_PERMISSIONS");
        }

        /* Check body */
        try {
            $body = $request->getContent();
        } catch (\Exception $e) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }
        $allTranslatesInput = $layer->deserialize($body, "array<DataBundle\Entity\CustomTranslate>");
        if (null === $allTranslatesInput) {
            return $response->error(400, "DESERIALIZE_ERROR");
        }

        foreach ($allTranslatesInput as $translateInput) {
            $langKey = $layer->getSingleResult('DataBundle:CustomTranslateAvailableLangs', ["langKey" => $translateInput->getLangKey()->getLangKey()]);
            if (null === $langKey) {
                return $this->get('response')->error(400, "LANG_NOT_FOUND");
            }

            $customTranslateDB = $layer->getSingleResult('DataBundle:CustomTranslate', [
                "keyId" => $translateInput->getKeyId(),
                "langKey" => $langKey
            ]);
            /* Eliminar clave */
            if (null === $customTranslateDB) {
                return $this->get('response')->error(400, "LANG_KEY_NOT_FOUND");
            } else {
                $customTranslateDB->setValue("");
                $em->remove($customTranslateDB);
            }
            $layer->wsPush($translateInput, "api_translations");
        }
        $em->flush();
        return $this->get('response')->success("LANG_KEY_DELETE_SUCCESS");
    }

    /**
     * View translates as a datatable.
     * @Rest\Post("/translates/table")
     */
    public function translatesCrudTableAction(Request $request)
    {
        $params = $request->request->all();
        $tables = $this->container->get("RouteLoader");
        $selectData = "DataBundle:CustomTranslate";
        $mainOrder = array("column" => "id", "dir" => "DESC");
        $data = $tables->generateTableResponse($params, $selectData, $mainOrder);
        return $this->get('response')->success("", $data);
    }

    /**
     * Get available translates.
     * @Rest\View()
     * @Rest\Get("/translates/available")
     */
    public function sessionTranslatesAvailableAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $translates = $layer->getAllResults('DataBundle:CustomTranslateAvailableLangs');
        return $this->get('response')->success("", $translates);
    }

    /**
     * Get default translate.
     * @Rest\View()
     * @Rest\Get("/translatedefault")
     */
    public function sessionTranslateDefaultAction(Request $request)
    {
        $layer = $this->get('rest.layer');
        $translate = $layer->getSingleResult('DataBundle:CustomTranslate', [
            "langKey" => $request->get("langKey")
        ]);
        return $this->get('response')->success("", $translate);
    }
}
