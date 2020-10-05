<?php

namespace Trax\Auth\Stores\Users;

use Illuminate\Support\Collection;

class UserSources
{
    /**
     * Return the data.
     *
     * @return array
     */
    public static function data(): array
    {
        return [
            [
                'id' => 'internal',
                'name' => __('trax-auth::options.user_sources_internal'),
            ],
            [
                'id' => 'ldap',
                'name' => __('trax-auth::options.user_sources_ldap'),
            ],
        ];
    }

    /**
     * Return the request rule.
     *
     * @return string
     */
    public static function rule(): string
    {
        return 'string|in:internal,ldap';
    }

    /**
     * Return the data collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all(): Collection
    {
        return collect(self::data());
    }
}
