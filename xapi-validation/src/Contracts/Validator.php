<?php

namespace Trax\XapiValidation\Contracts;

interface Validator
{
    /**
     * Validate a data and return a list of errors.
     *
     * @param  mixed  $data
     * @return array
     *
     * @throws \Trax\XapiValidation\Exceptions\XapiValidationException
     */
    public static function validate($data);

    /**
     * Tell if a data is valid.
     *
     * @param  mixed  $data
     * @return bool
     */
    public static function isValid($data): bool;
}
