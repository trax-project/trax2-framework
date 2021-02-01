<?php

namespace Trax\Auth\Stores\Clients;

use Trax\Repo\Contracts\ModelFactoryContract;
use Trax\Auth\Traits\FactoryWithPermissions;

class ClientFactory implements ModelFactoryContract
{
    use FactoryWithPermissions;

    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Client::class;
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
        $client = new $modelClass;

        // Required name.
        $client->name = $data['name'];

        // Optional boolean.
        if (isset($data['active'])) {
            $client->active = $data['active'];
        }

        // Optional boolean.
        if (isset($data['admin'])) {
            $client->admin = $data['admin'];
        }

        // Set permissions.
        self::setModelPermissions($client, $data);

        // Set default meta because there is no default value in the model.
        $client->meta = empty($data['meta']) ? [] : $data['meta'];

        // Nullable entity_id.
        if (isset($data['entity_id'])) {
            $client->entity_id = $data['entity_id'];
        }

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $client->owner_id = $data['owner_id'];
        }

        return $client;
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
        self::setModelPermissions($model, $data);
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
