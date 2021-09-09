<?php

namespace Trax\Auth;

class Password
{
    /**
     * Validate the password.
     *
     * @param  string  $password
     * @return bool
     */
    public static function validate(string $password): bool
    {
        return self::rules()::validate($password);
    }

    /**
     * Generate a random password.
     *
     * @return string
     */
    public static function random(): string
    {
        return self::rules()::random();
    }

    /**
     * Give password rules notice.
     *
     * @param  string  $password
     * @return string
     */
    public static function notice(string $password = null): string
    {
        return self::rules()::notice($password);
    }

    /**
     * Get the password rules class.
     *
     * @return string
     */
    protected static function rules(): string
    {
        return config(
            'trax-auth.user.password-rules',
            \Trax\Auth\Stores\Users\Auth\PasswordRules::class
        );
    }
}
