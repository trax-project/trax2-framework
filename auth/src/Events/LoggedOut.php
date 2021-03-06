<?php

namespace Trax\Auth\Events;

class LoggedOut
{
    /**
     * The logged out user.
     *
     * @var \Trax\Auth\Stores\Users\User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Trax\Auth\Stores\Users\User  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
