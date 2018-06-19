<?php

namespace RestBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Http\HttpUtils;
use DataBundle\Entity\User;

class AuthTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface {

    protected $container;
    protected $httpUtils;
    protected $em;

    public function __construct(ContainerInterface $container, HttpUtils $httpUtils, $em) {
        $this->container = $container;
        $this->httpUtils = $httpUtils;
        $this->em = $em;
    }

    public function createToken(Request $request, $providerKey) {
        $authorization = $request->headers->get('Authorization');

        $auth_explode = explode(' ', $authorization);
        $authTokenHeader = "";
        if (strtolower($auth_explode[0]) != "bearer") {
            $this->container->get('response')->error(401, "AUTH_HEADER_REQUIRED");
        }
        else {
            $authTokenHeader = $auth_explode[1];
        }

        $pre = new PreAuthenticatedToken('anon.', $authTokenHeader, $providerKey);
        $pre->setAuthenticated(false);

        $pre->setAttributes(array("pageUrl" => $request->getRequestUri()));
        return $pre;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
        $authToken = $this->digestToken($token, $userProvider);
        $user = $this->validateToken($authToken);
        $this->checkPermissions($user, $token->getAttributes()["pageUrl"]);

        $pre = new PreAuthenticatedToken($user, $token->getCredentials(), $providerKey, $user->getRoles());

        $pre->setAuthenticated(true);

        return $pre;
    }

    private function checkPermissions(User $user, $pageUrl) {
        try {
            $pagePermissions = $this->em->getRepository('DataBundle:PagePermissions')->findBy(array('pageUrl' => $pageUrl));
            $userPermissions = $this->em->getRepository('DataBundle:UserPermissions')->findBy(array('user' => $user));
            if (count($userPermissions) === 0) {
                $this->container->get('response')->error(500, "USER_PERMISSIONS_NOT_CONFIGURED");
            }
            else if (count($pagePermissions) === 0) {
                $this->container->get('response')->error(500, "REST_PERMISSIONS_NOT_CONFIGURED");
            }
            foreach ($pagePermissions as $pagePermission) {
                $hasPermission = false;
                foreach ($userPermissions as $userPermission) {
                    if ($userPermission->getPermission()->getId() === $pagePermission->getPermission()->getId()) {
                        $hasPermission = true;
                    }
                }
                if (!$hasPermission) {
                    $this->container->get('response')->error(403, "USER_HAS_NO_PERMISSIONS");
                }
            }
        }
        catch (\Doctrine\ORM\NoResultException $e) {
            $this->container->get('response')->error(500, "REST_PERMISSIONS_NOT_CONFIGURED");
        }
        catch (\Exception $e) {
            $this->container->get('response')->error(500, "REST_PERMISSIONS_CHECK");
        }
    }

    private function validateToken($authToken) {
        if($authToken === null || $authToken == "")
        {
            return new User();
        }
        try {
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
                $this->container->get('response')->error(406, "CONNECTED_OTHER_PC");
            }
            return $authToken->getUser();
        }
        catch (\Exception $e) {
            $this->container->get('response')->error(500, "REST_AUTH_VALIDATE");
        }
    }

    private function digestToken(TokenInterface $token, UserProviderInterface $userProvider) {
        try {
            $authToken = $userProvider->getAuthToken($token->getCredentials());

            if($authToken === null)
            {
                return false;
            }
            /* Check token expiry */
            if (null !== $authToken && $authToken->getExtendedSession()) {
                $expireTtl = $this->container->getParameter('web_extended_token_ttl');
            }
            else {
                $expireTtl = $this->container->getParameter('web_default_token_ttl');
            }
            /* Is token expired */
            if (!$authToken) {
                $this->container->get('response')->error(401, "INVALID_TOKEN");
            }
            if (!((time() - $authToken->getCreatedDate()->getTimestamp()) < $expireTtl)) {
                $this->container->get('response')->error(401, "EXPIRED_TOKEN");
            }
            return $authToken;
        }
        catch (\Exception $e) {
            $this->container->get('response')->error(500, "REST_AUTH_DIGEST");
        }
    }

    public function supportsToken(TokenInterface $token, $providerKey) {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, $exception) {
        throw $exception;
    }

}
