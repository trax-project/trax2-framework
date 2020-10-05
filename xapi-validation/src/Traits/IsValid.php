<?php

namespace Trax\XapiValidation\Traits;

use Trax\XapiValidation\Exceptions\XapiValidationException;

trait IsValid
{
    /**
     * Tell if a data is valid.
     *
     * @param  mixed  $data
     * @return bool
     */
    public static function isValid($data): bool
    {
        try {
            self::validate($data);
            return true;
        } catch (XapiValidationException $e) {
            return false;
        }
    }
}
