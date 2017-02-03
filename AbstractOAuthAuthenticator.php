<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

abstract class AbstractOAuthAuthenticator extends AbstractGuardAuthenticator implements OAuthManagerInterface
{
    /**
     * @var DataStorageInterface|null
     */
    private $dataStorage;

    /**
     * @var StateManagerInterface|null
     */
    private $stateManager;

    /**
     * @var OAuthServiceInterface[]
     */
    private $services;

    /**
     * @var OAuthServiceInterface
     */
    private $currentService;

    /**
     * @param DataStorageInterface|null  $dataStorage
     * @param StateManagerInterface|null $stateManager
     */
    public function __construct(DataStorageInterface $dataStorage = null, StateManagerInterface $stateManager = null)
    {
        $this->dataStorage = $dataStorage;
        $this->stateManager = $stateManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function registerService(OAuthServiceInterface $service)
    {
        $name = $service->getName();

        if (isset($this->services[$name])) {
            throw new \RuntimeException(
                sprintf('Service "%s" is already registered in "%s".', $name, get_class($this))
            );
        }

        $this->services[$name] = $service;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getLoginUrl($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new \RuntimeException(
                sprintf('Service "%s" is not registered in "%s".', $serviceName, get_class($this))
            );
        }

        $redirectUrl = $this->getRedirectUrl($serviceName);
        $state = $this->stateManager ? $this->stateManager->getState() : null;

        return $this->services[$serviceName]->getLoginUrl($redirectUrl, $state);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataStorage()
    {
        return $this->dataStorage;
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

        $this->currentService = $this->services[$name];

        if ($this->stateManager && $this->currentService->supportsState()) {
            $state = $this->currentService->getState($request);

            if (!$this->stateManager->isStateValid($state)) {
                return null;
            }
        }

        return $this->currentService->getCredentials($request);
    }

    /**
     * {@inheritdoc}
     */
    final public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $redirectUrl = $this->getRedirectUrl($this->currentService->getName());

        $data = $this->currentService->getData($credentials, $redirectUrl);
        $data->service = $this->currentService->getName();

        try {
            return $this->findUser($data, $userProvider);
        } finally {
            if (null !== $this->dataStorage) {
                $this->dataStorage->setData($data);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    abstract protected function getServiceName(Request $request);

    /**
     * @param OAuthData             $data
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException
     */
    abstract protected function findUser(OAuthData $data, UserProviderInterface $userProvider);
}
