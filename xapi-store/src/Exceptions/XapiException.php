<?php

namespace Trax\XapiStore\Exceptions;

use Exception;
use Trax\Core\Contracts\HttpException;

class XapiException extends Exception implements HttpException
{
    /**
     * Status code that should be inserted in the HTTP response.
     *
     * @var int
     */
    protected $status = 500;

    /**
     * Headers that should be inserted in the HTTP response.
     *
     * @var array
     */
    protected $headers = ['X-Experience-API-Version' => '1.0.3'];

    /**
     * Default validation message.
     *
     * @var string
     */
    protected $message = 'xAPI Exception.';

    /**
     * Errors associated with the exception.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Create an xAPI exception.
     *
     * @param  string  $message
     * @return void
     */
    public function __construct($message = '')
    {
        $message = empty($message) ? $this->message : $message;
        parent::__construct($message);
    }

    /**
     * Add headers to the HTTP response.
     *
     * @param  array  $headers
     * @return \Trax\XapiStore\Exceptions\XapiException
     */
    public function addHeaders(array $headers): XapiException
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set errors.
     *
     * @param  array  $errors
     * @return \Trax\XapiStore\Exceptions\XapiException
     */
    public function setErrors(array $errors): XapiException
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Get the status.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get the errors.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
