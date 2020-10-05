<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\XapiStore\Abstracts\XapiDocumentRequest;

class XapiAgentProfileRequest extends XapiDocumentRequest
{
    /**
     * Return the property name used to identify a document.
     *
     * @return string
     */
    public static function identifier(): string
    {
        return 'profileId';
    }

    /**
     * Get data to be recorded.
     *
     * @return array
     */
    public function data(): array
    {
        return [
            'agent' => is_string($this->param('agent'))
                ? json_decode($this->param('agent'), true)
                : $this->param('agent'),
            
            'profile_id' => $this->param('profileId'),
            'data' => $this->content(),
        ];
    }
}
