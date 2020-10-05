<?php

namespace Trax\XapiStore\Exceptions;

class XapiNotFoundException extends XapiException
{
    /**
     * Status code that should be inserted in the HTTP response.
     *
     * @var int
     */
    protected $status = 404;

    /**
     * Default validation message.
     *
     * @var string
     */
    protected $message = 'xAPI Resource Not Found.';
}
