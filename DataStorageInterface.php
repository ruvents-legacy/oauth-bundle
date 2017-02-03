<?php

namespace Ruvents\OAuthBundle;

interface DataStorageInterface
{
    /**
     * @param OAuthData $data
     */
    public function setData(OAuthData $data);

    /**
     * @return bool
     */
    public function hasData();

    /**
     * @return OAuthData
     */
    public function getData();

    public function removeData();
}
