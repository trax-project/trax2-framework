<?php

namespace Trax\Auth\Contracts;

interface ConsumerContract
{
    /**
     * Is it a user?
     *
     * @return bool
     */
    public function isUser(): bool;

    /**
     * Is it an app?
     *
     * @return bool
     */
    public function isApp(): bool;
}
