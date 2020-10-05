<?php

namespace Trax\XapiStore\Stores\States;

use Trax\XapiStore\Abstracts\XapiDocumentRequest;

class XapiStateRequest extends XapiDocumentRequest
{
    /**
     * Return the property name used to identify a document.
     *
     * @return string
     */
    public static function identifier(): string
    {
        return 'stateId';
    }

    /**
     * Get data to be recorded.
     *
     * @return array
     */
    public function data(): array
    {
        $data = [
            'activity_id' => $this->param('activityId'),

            'agent' => is_string($this->param('agent'))
                ? json_decode($this->param('agent'), true)
                : $this->param('agent'),
            
            'state_id' => $this->param('stateId'),
            'data' => $this->content(),
        ];

        if ($registration = $this->param('registration')) {
            $data['registration'] = $registration;
        }

        return $data;
    }
}
