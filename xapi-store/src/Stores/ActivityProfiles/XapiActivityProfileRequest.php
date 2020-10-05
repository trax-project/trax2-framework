<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\XapiStore\Abstracts\XapiDocumentRequest;

class XapiActivityProfileRequest extends XapiDocumentRequest
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
            'activity_id' => $this->param('activityId'),
            'profile_id' => $this->param('profileId'),
            'data' => $this->content(),
        ];
    }
}
