<?php

namespace Trax\Auth\Stores\Users;

use Trax\Core\Options;

class UserSources extends Options
{
    /**
     * Return the data.
     *
     * @return array
     */
    public function data(): array
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
    public function rule(): string
    {
        return 'string|in:internal,ldap';
    }
}
