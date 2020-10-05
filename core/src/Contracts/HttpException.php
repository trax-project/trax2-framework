<?php

namespace Trax\Core\Contracts;

interface HttpException
{
    /**
     * Get the status.
     *
     * @return int
     */
    public function status(): int;

    /**
     * Get the headers.
     *
     * @return array
     */
    public function headers(): array;

    /**
     * Get the errors.
     *
     * @return array
     */
    public function errors(): array;
}
