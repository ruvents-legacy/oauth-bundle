<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\HttpFoundation\Request;

interface OAuthServiceInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $redirectUrl
     *
     * @return string
     */
    public function getLoginUrl($redirectUrl);

    /**
     * @param Request $request
     *
     * @return null|mixed
     */
    public function getCredentials(Request $request);

    /**
     * @param mixed  $credentials
     * @param string $redirectUrl
     *
     * @return OAuthData
     */
    public function getData($credentials, $redirectUrl);
}
