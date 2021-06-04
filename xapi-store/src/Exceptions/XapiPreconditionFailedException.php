<?php

namespace Trax\XapiStore\Exceptions;

class XapiPreconditionFailedException extends XapiException
{
    /**
     * Status code that should be inserted in the HTTP response.
     *
     * @var int
     */
    protected $status = 412;

    /**
     * Create a bad request exception.
     *
     * @param  string  $message
     * @param  array  $errors
     * @return void
     */
    public function __construct($message = 'xAPI Precondition Failed.')
    {
        parent::__construct($message);
    }
}
