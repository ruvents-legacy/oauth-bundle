<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class AbstractOAuthAuthenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var null|DataStorageInterface
     */
    private $dataStorage;

    /**
     * @param array                     $options
     * @param SessionInterface          $session
     * @param null|DataStorageInterface $dataStorage
     */
    public function __construct(
        array $options = [],
        SessionInterface $session,
        DataStorageInterface $dataStorage = null
    ) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->session = $session;
        $this->dataStorage = $dataStorage;
    }

    /**
     * @return string
     */
    abstract public function getRedirectUrl();

    /**
     * {@inheritdoc}
     */
    final public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $serviceCredentials = $this->getServiceCredentials($credentials, $this->getRedirectUrl());

        $user = $this->findUserByServiceCredentials($serviceCredentials, $userProvider);

        if (null !== $user) {
            return $user;
        }

        $data = $this->getData($serviceCredentials);

        if (null === $data) {
            return null;
        }

        $user = $this->findUserByData($data, $userProvider);

        if (null === $user && null !== $this->dataStorage && $this->saveUserDataOnUserNotFound()) {
            $this->dataStorage->save($data);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if (null === $targetPath = $this->getTargetPath($this->session, $providerKey)) {
            return null;
        }

        return new RedirectResponse($targetPath);
    }

    /**
     * @param mixed  $requestCredentials
     * @param string $redirectUrl
     *
     * @return mixed|null
     */
    abstract protected function getServiceCredentials($requestCredentials, $redirectUrl);

    /**
     * @param mixed                 $serviceCredentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     */
    abstract protected function findUserByServiceCredentials($serviceCredentials, UserProviderInterface $userProvider);

    /**
     * @param mixed $serviceCredentials
     *
     * @return DataInterface
     */
    abstract protected function getData($serviceCredentials);

    /**
     * @param DataInterface         $data
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     */
    abstract protected function findUserByData(DataInterface $data, UserProviderInterface $userProvider);

    /**
     * @return bool
     */
    abstract protected function saveUserDataOnUserNotFound();

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
    }
}
