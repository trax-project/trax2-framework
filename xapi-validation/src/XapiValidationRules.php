<?php

namespace Trax\XapiValidation;

use Trax\Core\Validation;

class XapiValidationRules
{
    /**
     * Register the rules.
     *
     * @return void
     */
    public static function register()
    {
        self::registerXapiAgentRule();
        self::registerXapiFormatRule();
        self::registerXapiLangMapRule();
        self::registerXapiMboxRule();
        self::registerXapiScaledRule();
        self::registerXapiVersionRule();
    }

    /**
     * xAPI agent rule.
     *
     * @return void
     */
    protected static function registerXapiAgentRule()
    {
        app('validator')->extend('xapi_agent', function ($attribute, $value, $parameters, $validator) {
            if (is_string($value)) {
                if (!$value = json_decode($value)) {
                    return false;
                }
            }
            return Agent::isValid($value);
        }, "Invalid xAPI agent.");
    }

    /**
     * xAPI format rule.
     *
     * @return void
     */
    protected static function registerXapiFormatRule()
    {
        app('validator')->extend('xapi_format', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            return in_array($value, ['ids', 'exact', 'canonical']);
        }, "Invalid xAPI Format. Must be 'ids', 'exact' or 'canonical'.");
    }

    /**
     * xAPI lang map rule.
     *
     * @return void
     */
    protected static function registerXapiLangMapRule()
    {
        app('validator')->extend('xapi_lang_map', function ($attribute, $value, $parameters, $validator) {
            if (is_string($value)) {
                $value = json_decode($value);
            }
            if (!$value || !is_object($value)) {
                return false;
            }
            $langs = get_object_vars($value);
            foreach ($langs as $lang => $string) {
                if (!is_string($string)) {
                    return false;
                }
                if (!Validation::check($lang, 'iso_lang')) {
                    return false;
                }
            }
            return true;
        }, "Invalid xAPI language map.");
    }

    /**
     * xAPI mbox rule.
     *
     * @return void
     */
    protected static function registerXapiMboxRule()
    {
        app('validator')->extend('xapi_mbox', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            $parts = explode(':', $value);
            return (count($parts) == 2 && $parts[0] == 'mailto' && Validation::check($parts[1], 'email'));
        }, "Invalid xAPI mbox.");
    }

    /**
     * xAPI scaled rule.
     *
     * @return void
     */
    protected static function registerXapiScaledRule()
    {
        app('validator')->extend('xapi_scaled', function ($attribute, $value, $parameters, $validator) {
            return (!is_string($value) && is_numeric($value) && $value <= 1 && $value >= -1);
        }, "Invalid xAPI scaled score.");
    }

    /**
     * xAPI version rule.
     *
     * @return void
     */
    protected static function registerXapiVersionRule()
    {
        app('validator')->extend('xapi_version', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            $version = explode('.', $value);
            if (count($version) < 2 || count($version) > 3 || $version[0] !== '1' || $version[1] !== '0') {
                return false;
            }
            return true;
        }, "Invalid xAPI version.");
    }
}
