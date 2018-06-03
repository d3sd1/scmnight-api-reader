<?php

namespace RestBundle\Utils;

use DataBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mailing {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function send(String $reciper, String $title, String $template, $templateExtra) {
        try {
            $message = (new \Swift_Message($title))
                    ->setFrom('mailer@scmnight.com')
                    ->setTo($reciper)
                    ->setBody($this->renderView('EmailBundle::' . $template . '.html.twig', $templateExtra));
            $this->get('mailer')->send($message);
        }
        catch (Symfony\Component\Debug\Exception\SilencedErrorContext $e) {
            $this->container->get('sendlog')->warning('Error when sending email: ' . $e);
        }
    }

}
