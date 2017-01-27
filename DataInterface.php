<?php

namespace Ruvents\OAuthBundle;

use Symfony\Component\Security\Core\User\UserInterface;

interface DataInterface
{
    /**
     * @param UserInterface $user
     *
     * @return void
     */
    public function updateUser(UserInterface $user);
}
