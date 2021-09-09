<?php

namespace Trax\Auth\Stores\Users\Auth;

class PasswordRules
{
    /**
     * @var int
     */
    protected static $length = 8;

    /**
     * Validate the password.
     *
     * @param  string  $password
     * @return bool
     */
    public static function validate(string $password): bool
    {
        return self::length($password);
    }

    /**
     * Generate a random password.
     *
     * @return string
     */
    public static function random(): string
    {
        return \Str::random(self::$length);
    }

    /**
     * Give password rules notice.
     *
     * @param  string  $password
     * @return string
     */
    public static function notice(string $password = null): string
    {
        if (!isset($password) || self::validate($password)) {
            return self::$length . ' characters alpha-num string';
        }
        if (!self::length($password)) {
            return 'The password must have at least ' . self::$length . ' characters.';
        }
    }

    /**
     * Check the length.
     *
     * @param  string  $password
     * @return bool
     */
    protected static function length($password): bool
    {
        return \Str::of($password)->length() >= self::$length;
    }
}
