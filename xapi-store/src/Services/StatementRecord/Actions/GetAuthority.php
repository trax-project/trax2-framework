<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Trax\Auth\TraxAuth;

trait GetAuthority
{
    /**
     * Get the client autority.
     *
     * @return object
     */
    protected function getAccessAuthority(): object
    {
        $authority = config('trax-xapi-store.authority', [
            'name' => 'authority',
            'homePage' => 'http://traxlrs.com',
        ]);

        $access = TraxAuth::access();

        if (!is_null($access)
            && isset($access->client->meta['authority'])
            && !empty($access->client->meta['authority']['name'])
            && !empty($access->client->meta['authority']['homePage'])
        ) {
            $authority = [
                'name' => $access->client->meta['authority']['name'],
                'homePage' => $access->client->meta['authority']['homePage'],
            ];
        }

        return (object)[
            'objectType' => 'Agent',
            'account' => (object)$authority
        ];
    }

    /**
     * Get the autority.
     *
     * @return object
     */
    protected function getImportAuthority(): object
    {
        $authority = config('trax-lrs.importer.authority', false);

        if (!$authority) {
            $authority = config('trax-xapi-store.authority', [
                'name' => 'authority',
                'homePage' => 'http://traxlrs.com',
            ]);
        }

        return (object)[
            'objectType' => 'Agent',
            'account' => (object)$authority
        ];
    }
}
