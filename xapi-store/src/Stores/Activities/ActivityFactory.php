<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Repo\Contracts\ModelFactoryContract;

class ActivityFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Activity::class;
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

        // Data as an object.
        if (is_array($data['data'])) {
            $data['data'] = json_decode(json_encode($data['data']));
        }

        // Object Type.
        $data['data']->objectType = 'Activity';

        // Required data.
        $model->data = $data['data'];

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $model->owner_id = $data['owner_id'];
        }

        // IRI.
        $model->iri = $model->data->id;

        return $model;
    }

    /**
     * Prepare data before recording (used for bulk insert).
     *
     * @param array  $data
     * @return array
     */
    public static function prepare(array $data)
    {
        // Data as an object.
        $data['data'] = json_decode(json_encode($data['data']));

        // Object Type.
        $data['data']->objectType = 'Activity';

        // IRI.
        $data['iri'] = $data['data']->id;

        // JSON data.
        $data['data'] = json_encode($data['data']);

        // Timestamps.
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $data;
    }

    /**
     * Merge data with an existing model instance.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return bool
     */
    public static function merge($model, array $data): bool
    {
        // Nothing in the definition.
        if (!isset($data['data']->definition)) {
            return false;
        }

        // Nothing new in the data.
        if (isset($model->data->definition)
            && self::objectIncludes($model->data->definition, $data['data']->definition)
        ) {
            return false;
        }

        $newData = $model->data;

        // Add definition to existing activity.
        if (!isset($newData->definition)) {
            $newData->definition = (object)[];
        }

        // Merge properties.
        $props = get_object_vars($data['data']->definition);
        foreach ($props as $prop => $value) {
            if (in_array($prop, ['name', 'description']) && isset($newData->definition->$prop)) {
                // Merge lang map.
                $langs = get_object_vars($data['data']->definition->$prop);
                foreach ($langs as $lang => $text) {
                    $newData->definition->$prop->$lang = $text;
                }
            } else {
                // Other.
                $newData->definition->$prop = $value;
            }
        }
        $model->data = $newData;
        
        return true;
    }
    
    /**
     * Check if source object includes a target object.
     *
     * @param  object|array  $source
     * @param  object|array  $target
     * @return bool
     */
    public static function objectIncludes($source, $target): bool
    {
        // Source and target should always be objects.
        // However, an empty object may be transformed in an empty array when decoding JSON.
        // So we check this...
        $source = is_array($source) ? (object)$source : $source;
        $target = is_array($target) ? (object)$target : $target;

        $sourceProps = get_object_vars($source);
        $targetProps = get_object_vars($target);

        // The target has more props.
        if (!empty(array_diff(array_keys($targetProps), array_keys($sourceProps)))) {
            return false;
        }

        // Check all the target props.
        foreach ($targetProps as $key => $value) {

            // Not the same type.
            if (gettype($value) != gettype($sourceProps[$key])) {
                return false;
            }

            // Objects.
            if (is_object($value) && !self::objectIncludes($sourceProps[$key], $value)) {
                return false;
            }

            // Arrays.
            if (is_array($value)) {
                // The comparison cost is too high. Return false.
                return false;
            }

            // Other.
            if ($value != $sourceProps[$key]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Update an existing model instance, given some data.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function update($model, array $data)
    {
        $model->update($data);
        return $model;
    }

    /**
     * Duplicate an existing model in the database, given some data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function duplicate($model, array $data = [])
    {
        $copy = $model->replicate()->fill($data);
        $copy->save();
        return $copy;
    }
}
