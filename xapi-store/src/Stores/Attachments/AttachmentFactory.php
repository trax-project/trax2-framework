<?php

namespace Trax\XapiStore\Stores\Attachments;

use Trax\Repo\Contracts\ModelFactoryContract;

class AttachmentFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Attachment::class;
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
}
