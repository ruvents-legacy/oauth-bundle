<?php

namespace Ruvents\OAuthBundle;

interface OAuthManagerInterface
{
    /**
     * @param OAuthServiceInterface $service
     */
    public function registerService(OAuthServiceInterface $service);

    /**
     * @return DataStorageInterface
     */
    public function getDataStorage();

    /**
     * @param string $serviceName
     *
     * @return string
     */
    public function getRedirectUrl($serviceName);

    /**
     * @param string $serviceName
     *
     * @return string
     */
    public function getLoginUrl($serviceName);
}
