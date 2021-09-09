<?php

namespace Trax\Core;

class ValidationRules
{
    /**
     * Register the rules.
     *
     * @return void
     */
    public static function register()
    {
        self::registerCustomPasswordRule();
        self::registerIriRule();
        self::registerIsoDateRule();
        self::registerIsoDurationRule();
        self::registerIsoLangRule();
        self::registerContentTypeRule();
        self::registerUuidRule();
        self::registerJsonBooleanRule();
        self::registerStrictNumericRule();
        self::registerStrictIntegerRule();
        self::registerForbiddenRule();
        self::registerForbiddenWithRule();
        self::registerArrayOrJsonRule();
    }

    /**
     * Custom password.
     *
     * @return void
     */
    protected static function registerCustomPasswordRule()
    {
        app('validator')->extend('custom_password', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            return \Trax\Auth\Password::validate($value);
        }, \Trax\Auth\Password::notice());
    }

    /**
     * IRI rule.
     *
     * @return void
     */
    protected static function registerIriRule()
    {
        app('validator')->extend('iri', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            return (bool)preg_match('/^\w+:/i', $value);
        }, "Invalid IRI.");
    }

    /**
     * ISO 8601 timestamp rule.
     * From http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
     *
     * @return void
     */
    protected static function registerIsoDateRule()
    {
        app('validator')->extend('iso_date', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            return (bool)preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|((?!-0{2}(:0{2})?)([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?)?$/', $value);
        }, "Invalid ISO 8601 date.");
    }

    /**
     * ISO 8601 duration rule.
     *
     * @return void
     */
    protected static function registerIsoDurationRule()
    {
        app('validator')->extend('iso_duration', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            $classic = (bool) preg_match('/^P((\d+([\.,]\d+)?Y)?(\d+([\.,]\d+)?M)?(\d+([\.,]\d+)?D)?)?(T(\d+([\.,]\d+)?H)?(\d+([\.,]\d+)?M)?(\d+([\.,]\d+)?S)?)?$/i', $value);
            $weeks = (bool) preg_match('/^P\d+W$/i', $value);
            return $classic || $weeks;
        }, "Invalid ISO 8601 duration.");
    }

    /**
     * ISO lang rule.
     *
     * @return void
     */
    protected static function registerIsoLangRule()
    {
        app('validator')->extend('iso_lang', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            
            return (bool) preg_match('/^((?:(en-GB-oed|i-ami|i-bnn|i-default|i-enochian|i-hak|i-klingon|i-lux|i-mingo|i-navajo|i-pwn|i-tao|i-tay|i-tsu|sgn-BE-FR|sgn-BE-NL|sgn-CH-DE)|(art-lojban|cel-gaulish|no-bok|no-nyn|zh-guoyu|zh-hakka|zh-min|zh-min-nan|zh-xiang))|((?:([A-Za-z]{2,3}(-(?:[A-Za-z]{3}(-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(-(?:[A-Za-z]{4}))?(-(?:[A-Za-z]{2}|[0-9]{3}))?(-(?:[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(-(?:[0-9A-WY-Za-wy-z](-[A-Za-z0-9]{2,8})+))*(-(?:x(-[A-Za-z0-9]{1,8})+))?)|(?:x(-[A-Za-z0-9]{1,8})+))$/i', $value);
        }, "Invalid RFC 5646 language tag.");
    }

    /**
     * HTTP content-type rule.
     *
     * @return void
     */
    protected static function registerContentTypeRule()
    {
        app('validator')->extend('content_type', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            return (bool) preg_match('#^(application|audio|example|image|message|model|multipart|text|video)(/[-\w\+]+)(;\s*[-\w]+\=[-\w]+)*;?$#', $value);
        }, "Invalid Content-Type.");
    }

    /**
     * UUID rule.
     *
     * @return void
     */
    protected static function registerUuidRule()
    {
        app('validator')->extend('uuid', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
        }, "Invalid UUID.");
    }

    /**
     * Boolean flex rule ("true" & "false" allowed).
     *
     * @return void
     */
    protected static function registerJsonBooleanRule()
    {
        app('validator')->extend('json_boolean', function ($attribute, $value, $parameters, $validator) {
            return ($value == 'true' || $value == 'false');
        }, "Invalid boolean.");
    }

    /**
     * Numeric strict rule (string not allowed).
     *
     * @return void
     */
    protected static function registerStrictNumericRule()
    {
        app('validator')->extend('strict_numeric', function ($attribute, $value, $parameters, $validator) {
            return (!is_string($value) && Validation::check($value, 'numeric'));
        }, "Invalid numeric.");
    }

    /**
     * Integer strict rule (string not allowed).
     *
     * @return void
     */
    protected static function registerStrictIntegerRule()
    {
        app('validator')->extend('strict_integer', function ($attribute, $value, $parameters, $validator) {
            return (!is_string($value) && Validation::check($value, 'integer'));
        }, "Invalid integer.");
    }

    /**
     * Forbidden rule.
     *
     * @return void
     */
    protected static function registerForbiddenRule()
    {
        app('validator')->extend('forbidden', function ($attribute, $value, $parameters, $validator) {
            return is_null($value);
        }, "Forbidden property.");
    }

    /**
     * Forbidden with rule.
     *
     * @return void
     */
    protected static function registerForbiddenWithRule()
    {
        app('validator')->extend('forbidden_with', function ($attribute, $value, $parameters, $validator) {
            $attributes = array_keys($validator->attributes());
            return (is_null($value) || empty(array_intersect($attributes, $parameters)));
        }, "Forbidden property in this context.");
    }

    /**
     * Array or JSON string rule.
     *
     * @return void
     */
    protected static function registerArrayOrJsonRule()
    {
        app('validator')->extend('array_or_json', function ($attribute, $value, $parameters, $validator) {
            $casted = is_string($value) ? json_decode($value, true) : $value;
            return is_array($casted) || is_object($casted);
        }, "Forbidden property in this context.");
    }
}
