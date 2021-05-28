<?php

namespace Trax\Auth\Events;

class ResetFailed
{
    /**
     * The reset email.
     *
     * @var string
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param  string  $email
     * @return void
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }
}
