<?php

namespace Ruvents\OAuthBundle;

interface DataStorageInterface
{
    /**
     * @param OAuthData $data
     */
    public function save(OAuthData $data);

    /**
     * @return bool
     */
    public function has();

    /**
     * @return OAuthData
     */
    public function get();

    public function clear();
}
