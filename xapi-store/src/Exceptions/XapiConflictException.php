<?php

namespace Trax\XapiStore\Exceptions;

class XapiConflictException extends XapiException
{
    /**
     * Status code that should be inserted in the HTTP response.
     *
     * @var int
     */
    protected $status = 409;

    /**
     * Create a bad request exception.
     *
     * @param  string  $message
     * @param  array  $errors
     * @return void
     */
    public function __construct($message = 'xAPI Conflict.')
    {
        parent::__construct($message);
    }
}
