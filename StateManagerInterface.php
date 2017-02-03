<?php

namespace Ruvents\OAuthBundle;

interface StateManagerInterface
{
    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $state
     *
     * @return bool
     */
    public function isStateValid($state);
}
