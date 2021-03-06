<?php

namespace WebSocketsBundle\Topic;

use Doctrine\ORM\EntityManager;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatUsersTopic implements TopicInterface, PushableTopicInterface
{

    private $container;
    private $em;
    private $layer;
    function __construct(EntityManager $em, ContainerInterface $container, $layer)
    {
        $this->em = $em;
        $this->container = $container;
        $this->layer = $layer;
    }

    /**
     * This will receive any Subscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {

    }

    /**
     * This will receive any UnSubscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {

    }


    /**
     * This will receive any Publish requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param $event
     * @param array $exclude
     * @param array $eligible
     * @return mixed|void
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $data = json_decode($event, true); //ya que se serializa internamente en gos...
        $topic->broadcast($data);
    }

    /**
    * Like RPC is will use to prefix the channel
    * @return string
    */
    public function getName()
    {
        return 'chat.users.topic';
    }
    
    /**
     * @param Topic        $topic
     * @param WampRequest  $request
     * @param array|string $data
     * @param string       $provider The name of pusher who push the data
     */
    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        $topic->broadcast($data);
    }
}