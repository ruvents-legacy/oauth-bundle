<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionDataStorage implements DataStorageInterface
{
    const KEY = 'ruvents_oauth.data';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->session->get(self::KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function save(DataInterface $data)
    {
        $this->session->set(self::KEY, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->session->remove(self::KEY);
    }
}
