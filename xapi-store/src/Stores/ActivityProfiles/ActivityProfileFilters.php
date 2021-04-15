<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\XapiStore\Traits\XapiDocumentFilters;

trait ActivityProfileFilters
{
    use XapiDocumentFilters;
    
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            // xAPI standard filters.
            'profileId', 'activityId', 'since'
        ];
    }
}
