<?php

namespace RestBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Http\HttpUtils;
use DataBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{

    protected $container;
    protected $httpUtils;
    protected $em;
    protected $request;

    public function __construct(ContainerInterface $container, HttpUtils $httpUtils, $em)
    {
        $this->container = $container;
        $this->httpUtils = $httpUtils;
        $this->em = $em;
    }

    public function createToken(Request $request, $providerKey)
    {
        $authorization = $request->headers->get('Authorization');

        $this->request = $request;
        $auth_explode = explode(' ', $authorization);
        if (strtolower($auth_explode[0]) != "bearer") {
            throw new BadCredentialsException("AUTH_HEADER_REQUIRED");
        } else {
            $authTokenHeader = $auth_explode[1];
        }

        $pre = new PreAuthenticatedToken('anon.', $authTokenHeader, $providerKey);
        $pre->setAuthenticated(false);
        return $pre;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof UserProviderInterface)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of AppTokenProvider (%s was given).', get_class($userProvider)
                )
            );
        }

        $authTokenHeader = $token->getCredentials();
        $authToken = $this->em->getRepository('RestBundle:AuthToken')->findOneBy(array('value' => $authTokenHeader));
        /* Is token expired */
        if(!$authToken) {
            throw new BadCredentialsException('INVALID_TOKEN');
        }
        if (!((time() - $authToken->getCreatedDate()->getTimestamp()) < $this->container->getParameter('app_token_ttl')))
        {
            throw new BadCredentialsException('EXPIRED_TOKEN');
        }

        if ($authToken === null || $authToken == "") {
            new User();
        }
        $lastAuthToken = $this->em->getRepository('RestBundle:AuthToken')
            ->createQueryBuilder("at")
            ->select('at.value as token')
            ->where('at.user = :user')
            ->setParameter("user", $authToken->getUser())
            ->orderBy('at.createdDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        if ($lastAuthToken["token"] != $authToken->getValue()) {
            throw new NotAcceptableHttpException('CONNECTED_OTHER_PC');
        }

        $pre = new PreAuthenticatedToken($authToken->getUser(), $token->getCredentials(), $providerKey, $authToken->getUser()->getRoles());

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
