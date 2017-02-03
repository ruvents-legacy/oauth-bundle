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
     * @param string      $redirectUrl
     * @param string|null $state
     *
     * @return string
     */
    public function getLoginUrl($redirectUrl, $state = null);

    /**
     * @param Request $request
     *
     * @return null|mixed
     */
    public function getCredentials(Request $request);

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getState(Request $request);

    /**
     * @param mixed  $credentials
     * @param string $redirectUrl
     *
     * @return OAuthData
     */
    public function getData($credentials, $redirectUrl);

    /**
     * @return bool
     */
    public function supportsState();
}
