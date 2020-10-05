<?php

namespace Trax\XapiValidation\Exceptions;

use Exception;
use Trax\Core\Contracts\HttpException;

class XapiValidationException extends Exception implements HttpException
{
    /**
     * Data to be validated.
     *
     * @var \stdClass|array
     */
    protected $data;

    /**
     * Validation errors.
     * Each error is: ['prop.with.error' => 'Error explanation.']
     *
     * @var array
     */
    protected $errors;

    /**
     * Create a bad request exception.
     *
     * @param  string  $message
     * @param  \stdClass|array  $data
     * @param  array  $errors
     * @return void
     */
    public function __construct($message = 'xAPI Validation Error(s).', $data = [], array $errors = [])
    {
        parent::__construct($message);
        $this->data = $data;
        $this->errors = $errors;
    }

    /**
     * Get the data.
     *
     * @return \stdClass|array
     */
    public function data()
    {
        return $this->data;
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

    /**
     * Get the status.
     *
     * @return int
     */
    public function status(): int
    {
        return 400;
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function headers(): array
    {
        return [];
    }
}
