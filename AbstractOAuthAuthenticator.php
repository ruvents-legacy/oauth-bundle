<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class AbstractOAuthAuthenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var OAuthServiceInterface[]
     */
    private $services;

    /**
     * @var OAuthServiceInterface
     */
    private $service;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param OAuthServiceInterface $service
     *
     * @throws \RuntimeException
     */
    final public function registerService(OAuthServiceInterface $service)
    {
        $name = $service->getName();

        if (isset($this->services[$name])) {
            throw new \RuntimeException(sprintf(
                'Service "%s" is already registered in "%s".',
                $name, get_class($this)
            ));
        }

        $this->services[$name] = $service;
    }

    /**
     * @param string $serviceName
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    final public function getLoginUrl($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new \RuntimeException(sprintf(
                'Service "%s" is not registered in "%s".',
                $serviceName,
                get_class($this)
            ));
        }

        $redirectUrl = $this->getRedirectUrl($serviceName);

        return $this->services[$serviceName]->getLoginUrl($redirectUrl);
    }

    /**
     * {@inheritdoc}
     */
    final public function getCredentials(Request $request)
    {
        $name = $this->getServiceName($request);

        if (!isset($this->services[$name])) {
            return null;
        }

        $this->service = $this->services[$name];

        return $this->service->getCredentials($request);
    }

    /**
     * {@inheritdoc}
     */
    final public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    final public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $redirectUrl = $this->getRedirectUrl($this->service->getName());

        $data = $this->service->getData($credentials, $redirectUrl);
        $data->service = $this->service->getName();

        $user = $this->findUser($data, $userProvider);

        if (null === $user) {
            $this->session->set($this->getLastDataSessionKey(), $data);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->getTargetPath($this->session, $providerKey));
    }

    /**
     * @return OAuthData|null
     */
    final public function getLastData()
    {
        return $this->session->get($this->getLastDataSessionKey());
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    abstract protected function getServiceName(Request $request);

    /**
     * @param string $serviceName
     *
     * @return string
     */
    abstract protected function getRedirectUrl($serviceName);

    /**
     * @param OAuthData             $data
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface
     */
    abstract protected function findUser(OAuthData $data, UserProviderInterface $userProvider);

    /**
     * @return string
     */
    private function getLastDataSessionKey()
    {
        return 'ruvents_oauth.'.md5(get_class($this));
    }
}
