<?php

namespace Ruvents\OAuthBundle;

class OAuthData
{
    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * @var int|string
     */
    public $id;

    /**
     * @var string
     */
    public $service;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $middleName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var array
     */
    public $extra = [];
}
