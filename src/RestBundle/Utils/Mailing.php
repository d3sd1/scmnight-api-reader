<?php

namespace RestBundle\Utils;

use DataBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mailing
{

    private $container;
    private $em;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
    }

    private function getAssetsUrl() {
        if($this->container->get('kernel')->getEnvironment() == "dev") {
            return "https://api.discoexample.dev.scmnight.com/";
        }
        else {
            return "https://scmnight.com/public/";
        }
    }
    function send(String $reciper, String $title, String $template, $templateExtra, $simpleText)
    {
        /* Common stuff */
        $templateExtra["assetsUrl"] = $this->getAssetsUrl();

        $discoName = $this->em->getRepository("DataBundle:Config")->findOneBy(["config" => "disco_name"]);
        $discoName = $discoName === null ? "":$discoName->getValue();
        $templateExtra["discoName"] = $discoName;

        $logo = $this->em->getRepository("DataBundle:ExtraConfig")->findOneBy(["config" => "base64_logo"]);
        $logo = $logo === null ? "":$logo->getValue();
        $templateExtra["logo"] = $logo;

        try {
            $bodyHtml = $this->container->get('twig')->render('EmailBundle::' . $template . '.html.twig', $templateExtra);
            $message = (new \Swift_Message($title))
                ->setFrom('mailer@scmnight.com', $discoName)
                ->setTo($reciper)
                ->setContentType("text/html")
                ->addPart($simpleText, 'text/plain')
                ->setBody($bodyHtml);
            $this->container->get('mailer')->send($message);
        } catch (\Symfony\Component\Debug\Exception\SilencedErrorContext $e) {
            $this->container->get('sendlog')->warning('Error when sending email: ' . $e);
        }
    }

}
