<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionStateManager implements StateManagerInterface
{
    const DEFAULT_KEY = 'ruvents_oauth.state';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $key;

    /**
     * @param SessionInterface $session
     * @param string           $key
     */
    public function __construct(SessionInterface $session, $key = self::DEFAULT_KEY)
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        if (!$this->session->has($this->key)) {
            $this->session->set($this->key, $this->generateState());
        }

        return $this->session->get($this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function isStateValid($state)
    {
        return $state === $this->getState();
    }

    /**
     * {@inheritdoc}
     */
    private function generateState()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
