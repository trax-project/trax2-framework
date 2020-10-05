<?php

namespace Trax\Core\Contracts;

interface Event
{
    /**
     * Return a log message.
     *
     * @return string
     */
    public function logMessage(): string;
}
