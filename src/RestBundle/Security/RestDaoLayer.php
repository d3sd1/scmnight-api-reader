<?php

namespace RestBundle\Security;


use Doctrine\ORM\Mapping\Entity;
use Psr\Container\ContainerInterface;

class RestDaoLayer
{

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function getSessionUserId()
    {
        $securityTokenManager = $this->container->get('security.token_storage');
        if (null === $securityTokenManager) {
            return null;
        } else if (null === $securityTokenManager->getToken()) {
            return null;
        } else if (null === $securityTokenManager->getToken()->getUser()) {
            return null;
        } else if (null === $securityTokenManager->getToken()->getUser()->getId()) {
            return null;
        } else {
            return $securityTokenManager->getToken()->getUser()->getId();
        }
    }

    public function getSessionUser()
    {
        return $this->getSingleResult('DataBundle:User', array('id' => "_SESSION_USER_INFO"));
    }

    private function depureConstants($by)
    {
        $constants = [
            "_SESSION_USER_INFO" => $this->getSessionUserId()
        ];
        foreach ($by as $field => $value) {
            if (array_key_exists($value, $constants)) {
                $by[$field] = $constants[$value];
            }
        }
        return $by;
    }

    public function validateSessionUserPassword($plainPassword)
    {
        $sessionUser = $this->getSessionUser();
        $encoder = $this->container->get('encoder.factory')->getEncoder($sessionUser);

        if (!$encoder->isPasswordValid($sessionUser->getPassword(), $plainPassword, $sessionUser->getSalt())) {
            return false;
        }
        return $this->validateUserPassword($sessionUser, $plainPassword);
    }

    public function validateUserPassword($userOnDb, $plainPassword)
    {
        $encoder = $this->container->get('encoder.factory')->getEncoder($userOnDb);

        if (!$encoder->isPasswordValid($userOnDb->getPassword(), $plainPassword, $userOnDb->getSalt())) {
            return false;
        }
        return true;
    }

    public function deserialize($body, $entity)
    {
        try {
            /** @var $entity $object */
            $deserialize = $this->container->get('jms_serializer')->deserialize($body, $entity, "json");
            if ($body === null || $body == "") {
                $deserialize = null;
            }
        } catch (\Exception $e) {
            $deserialize = null;
            $this->container->get('sendlog')->warning('DESERIALIZE FAILED: ' . $e->getMessage());
        }
        return $deserialize;
    }

    public function serialize($body)
    {
        try {
            /** @var $entity $object */
            $serialize = $this->container->get('jms_serializer')->serialize($body, "json");
            if ($body === null || $body == "") {
                $serialize = null;
            }
        } catch (\Exception $e) {
            $serialize = null;
            $this->container->get('sendlog')->warning('SERIALIZE FAILED: ' . $e->getMessage());
        }
        return $serialize;
    }

    public function getSingleResult($entity, $by)
    {
        try {
            $data = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository($entity)
                ->findOneBy($this->depureConstants($by));
        } catch (\Exception $e) {
            $data = null;
            $this->container->get('sendlog')->warning('GET SINGLE RESULT QUERY FAILED: ' . $e->getMessage());
        }
        return $data;
    }

    public function getMultiResults($entity, $by)
    {
        try {
            $data = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository($entity)
                ->findBy($this->depureConstants($by));
        } catch (\Exception $e) {
            $data = null;
            $this->container->get('sendlog')->warning('GET SINGLE RESULT QUERY FAILED: ' . $e->getMessage());
        }
        return $data;
    }

    public function getAllResults($entity)
    {
        try {
            $data = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository($entity)
                ->findAll();

        } catch (\Exception $e) {
            $data = null;
            $this->container->get('sendlog')->warning('GET ALL RESULTS QUERY FAILED: ' . $e->getMessage());
        }
        return $data;
    }

    public function getComplexResult($complexQuery)
    {
        try {
            $data = $complexQuery->getQuery()->getResult();

        } catch (\Exception $e) {
            var_dump($e->getMessage());die();
            $data = null;
            $this->container->get('sendlog')->warning('GET COMPLEX RESULT QUERY FAILED: ' . $e->getMessage());
        }
        return $data;
    }

    public function wsPush($data, $channel)
    {
        try {
            $pusher = $this->container->get('websockets.pusher');
            $pusher->push($this->serialize($data), $channel);
        } catch (\Exception $e) {
            $this->container->get('sendlog')->warning('WS SERVER OFFLINE: ' . $e->getMessage());
        }
    }

    public function getTranslate($keyId, $langKey)
    {
        $translate = $this->getSingleResult('DataBundle:CustomTranslate', [
            'keyId' => $keyId,
            'langKey' => $this->getSingleResult('DataBundle:CustomTranslateAvailableLangs', ['langKey' => $langKey])->getId()
        ]);

        return null !== $translate ? $translate->getValue() : "";
    }
}