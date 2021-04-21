<?php

namespace Trax\XapiStore\Exceptions;

class XapiNoContentException extends XapiException
{
    /**
     * Status code that should be inserted in the HTTP response.
     *
     * @var int
     */
    protected $status = 204;

    /**
     * Default validation message.
     *
     * @var string
     */
    protected $message = 'xAPI No Content.';
}
