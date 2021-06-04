<?php

namespace Trax\XapiStore\Stores\States;

use Trax\XapiStore\Abstracts\XapiDocumentFactory;
use Trax\XapiStore\XapiDate;
use Trax\XapiStore\Stores\Agents\AgentFactory;

class StateFactory extends XapiDocumentFactory
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return State::class;
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
        $model->state_id = $data['state_id'];
        $model->activity_id = $data['activity_id'];
        $model->vid = AgentFactory::virtualId($data['agent']);
        $model->data = $data['data'];

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $model->owner_id = $data['owner_id'];
        }

        // Nullable data.
        if (isset($data['registration'])) {
            $model->registration = $data['registration'];
        }

        // Generated data.
        $model->timestamp = XapiDate::now();

        return $model;
    }
}
