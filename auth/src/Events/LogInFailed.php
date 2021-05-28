<?php

namespace Trax\Auth\Events;

class LogInFailed
{
    /**
     * The logged user.
     *
     * @var string
     */
    public $username;

    /**
     * Create a new event instance.
     *
     * @param  string  $username
     * @return void
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }
}
