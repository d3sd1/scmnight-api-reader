<?php

namespace WebSocketsBundle\Topic;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class ClientsTopic implements TopicInterface, PushableTopicInterface
{

    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        
    }

    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        
    }

    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $data = json_decode($event, true); //ya que se serializa internamente en gos...
        $topic->broadcast($data);
    }

    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        $topic->broadcast($data);
    }

    public function getName()
    {
        return 'clients.topic';
    }

}
