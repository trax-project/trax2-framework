<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\XapiStore\Abstracts\XapiDocumentFactory;
use Trax\XapiStore\XapiDate;
use Trax\XapiStore\Stores\Agents\AgentFactory;

class AgentProfileFactory extends XapiDocumentFactory
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return AgentProfile::class;
    }

    /**
     * Create a new model instance given some data.
     *
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function make(array $data)
    {
        $modelClass = self::modelClass();
        $model = new $modelClass;

        // Required data.
        $model->profile_id = $data['profile_id'];
        $model->vid = AgentFactory::virtualId($data['agent']);
        $model->data = $data['data'];

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $model->owner_id = $data['owner_id'];
        }

        // Generated data.
        $model->timestamp = XapiDate::now();

        return $model;
    }
}
