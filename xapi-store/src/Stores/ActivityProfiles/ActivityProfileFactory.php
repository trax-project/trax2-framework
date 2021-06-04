<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\XapiStore\Abstracts\XapiDocumentFactory;
use Trax\XapiStore\XapiDate;

class ActivityProfileFactory extends XapiDocumentFactory
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return ActivityProfile::class;
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
        $model->activity_id = $data['activity_id'];
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
