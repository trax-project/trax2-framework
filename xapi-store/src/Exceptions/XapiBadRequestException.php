<?php

namespace Trax\XapiStore\Exceptions;

class XapiBadRequestException extends XapiException
{
    /**
     * Status code that should be inserted in the HTTP response.
     *
     * @var int
     */
    protected $status = 400;

    /**
     * Create a bad request exception.
     *
     * @param  string  $message
     * @param  array  $errors
     * @return void
     */
    public function __construct($message = 'xAPI Bad Request Exception.', array $errors = [])
    {
        parent::__construct($message);
        $this->setErrors($errors);
    }
}
