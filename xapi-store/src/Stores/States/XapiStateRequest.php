<?php

namespace Trax\XapiStore\Stores\States;

use Trax\XapiStore\Abstracts\XapiDocumentRequest;

class XapiStateRequest extends XapiDocumentRequest
{
    /**
     * Make a request.
     *
     * @param  array  $params
     * @param  object|array|null  $content
     * @param  string  $method
     * @return void
     */
    public function __construct(array $params, $content = null, string $method = null)
    {
        parent::__construct($params, $content, 'state', $method);
    }

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
