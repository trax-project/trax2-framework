<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Repo\Contracts\ModelFactoryContract;
use Trax\XapiStore\XapiDate;

class StatementFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Statement::class;
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

        // Statement ID.
        if (!isset($data['data']->id)) {
            $data['data']->id = \Str::uuid();
        }

        // Stored.
        $data['data']->stored = XapiDate::now();

        // Timestamp.
        if (!isset($data['data']->timestamp)) {
            $data['data']->timestamp = $data['data']->stored;
        }
        
        // Version.
        if (!isset($data['data']->version)) {
            $data['data']->version = '1.0.0';
        }
        
        // Normalize context activities.
        if (isset($data['data']->context) && isset($data['data']->context->contextActivities)) {
            self::normalizeContextActivities($data['data']->context->contextActivities);
        }
        if (isset($data['data']->object->objectType)
            && $data['data']->object->objectType == 'SubStatement'
            && isset($data['data']->object->context)
            && isset($data['data']->object->context->contextActivities)
        ) {
                self::normalizeContextActivities($data['data']->object->context->contextActivities);
        }

        // Required data.
        $model->data = $data['data'];

        // Indexes (optional).
        if (isset($data['access_id'])) {
            $model->access_id = $data['access_id'];
        }
        if (isset($data['client_id'])) {
            $model->client_id = $data['client_id'];
        }
        if (isset($data['entity_id'])) {
            $model->entity_id = $data['entity_id'];
        }

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $model->owner_id = $data['owner_id'];
        }

        // Nullable voided.
        if (isset($data['voided'])) {
            $model->voided = $data['voided'];
        }

        // UUID.
        $model->uuid = $model->data->id;

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

        // Statement ID.
        if (!isset($data['data']->id)) {
            $data['data']->id = (string) \Str::uuid();
        }

        // Stored.
        $data['data']->stored = XapiDate::now();

        // Timestamp.
        if (!isset($data['data']->timestamp)) {
            $data['data']->timestamp = $data['data']->stored;
        }
        
        // Version.
        if (!isset($data['data']->version)) {
            $data['data']->version = '1.0.0';
        }

        // Normalize context activities.
        if (isset($data['data']->context) && isset($data['data']->context->contextActivities)) {
            self::normalizeContextActivities($data['data']->context->contextActivities);
        }
        if (isset($data['data']->object->objectType)
            && $data['data']->object->objectType == 'SubStatement'
            && isset($data['data']->object->context)
            && isset($data['data']->object->context->contextActivities)
        ) {
                self::normalizeContextActivities($data['data']->object->context->contextActivities);
        }

        // UUID.
        $data['uuid'] = $data['data']->id;

        // JSON data.
        $data['data'] = json_encode($data['data']);

        // Timestamps.
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $data;
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
     * Normalize context activities.
     *
     * @param  \stdClass  $contextActivities
     * @return void
     */
    protected static function normalizeContextActivities(\stdClass $contextActivities)
    {
        if (isset($contextActivities->parent) && !is_array($contextActivities->parent)) {
            $contextActivities->parent = [$contextActivities->parent];
        }
        if (isset($contextActivities->grouping) && !is_array($contextActivities->grouping)) {
            $contextActivities->grouping = [$contextActivities->grouping];
        }
        if (isset($contextActivities->category) && !is_array($contextActivities->category)) {
            $contextActivities->category = [$contextActivities->category];
        }
        if (isset($contextActivities->other) && !is_array($contextActivities->other)) {
            $contextActivities->other = [$contextActivities->other];
        }
    }
}
