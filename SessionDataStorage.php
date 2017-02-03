<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionDataStorage implements DataStorageInterface
{
    const DEFAULT_KEY = 'ruvents_oauth.data';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $key;

    public function __construct(SessionInterface $session, $key = self::DEFAULT_KEY)
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function save(OAuthData $data)
    {
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $this->session->set($this->key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function has()
    {
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        return $this->session->has($this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        if (!$this->has()) {
            throw new \RuntimeException(sprintf('OAuth data for the key "%s" was not found.', $this->key));
        }

        return $this->session->get($this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $this->session->remove($this->key);
    }
}
