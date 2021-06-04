<?php

namespace Trax\XapiStore\Traits;

trait XapiAgentFilter
{
    /**
     * Get the filtering conditions for a given path and a searched agent.
     *
     * @param  string  $path
     * @param  array  $agent
     * @return array
     */
    protected function agentFilterConditions(string $path, array $agent): array
    {
        // Mbox.
        if (isset($agent['mbox'])) {
            return [$path.'->mbox' => $agent['mbox']];
        }
        // mbox_sha1sum.
        if (isset($agent['mbox_sha1sum'])) {
            return [$path.'->mbox_sha1sum' => $agent['mbox_sha1sum']];
        }
        // openid.
        if (isset($agent['openid'])) {
            return [$path.'->openid' => $agent['openid']];
        }
        // Account.
        if (isset($agent['account'])) {
            return ['$and' => [
                $path.'->account->name' => $agent['account']['name'],
                $path.'->account->homePage' => $agent['account']['homePage'],
            ]];
        }
    }
}
