<?php

namespace AppBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class AppTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{

    protected $container;
    protected $httpUtils;
    protected $em;

    public function __construct(ContainerInterface $container, HttpUtils $httpUtils, $em)
    {
        $this->container = $container;
        $this->httpUtils = $httpUtils;
        $this->em = $em;
    }

    public function createToken(Request $request, $providerKey)
    {
        $authorization = $request->headers->get('Authorization');
        if (!$authorization)
        {
            throw new BadCredentialsException('Authorization header is required');
        }
        else
        {
            $auth_explode = explode(' ', $authorization);
            if (strtolower($auth_explode[0]) != "bearer")
            {
                throw new BadCredentialsException('Authorization Bearer is required');
            }
            else
            {
                $authTokenHeader = $auth_explode[1];
            }
        }

        return new PreAuthenticatedToken(
                'anon.', $authTokenHeader, $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $appProvider, $providerKey)
    {
        if (!$appProvider instanceof AppTokenProvider)
        {
            throw new \InvalidArgumentException(
            sprintf(
                    'The user provider must be an instance of AppTokenProvider (%s was given).', get_class($appProvider)
            )
            );
        }

        $authTokenHeader = $token->getCredentials();
        $appToken = $appProvider->getAppToken($authTokenHeader);
        /* Is token expired */
        if (!$appToken || !((time() - $appToken->getCreatedDate()->getTimestamp()) < $this->container->getParameter('app_token_ttl')))
        {
            throw new BadCredentialsException('Invalid or expired authentication token');
        }
        $lastAuthToken = $this->em->getRepository('AppBundle:AppToken')
                ->createQueryBuilder("at")
                ->select('at.value as token')
                ->where('at.computer = :computer')
                ->setParameter("computer", $appToken->getComputer())
                ->orderBy('at.createdDate', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();

        if ($lastAuthToken["token"] != $appToken->getValue())
        {
            throw new NotAcceptableHttpException('Logged somewhere else');
        }

        $user = $appToken->getComputer();
        $pre = new PreAuthenticatedToken(
                $user, $authTokenHeader, $providerKey, $user->getRoles()
        );

        $pre->setAuthenticated(true);

        return $pre;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw $exception;
    }

}
