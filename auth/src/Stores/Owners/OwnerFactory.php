<?php

namespace Trax\Auth\Stores\Owners;

use Trax\Repo\Contracts\ModelFactoryContract;

class OwnerFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Owner::class;
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
        $owner = new $modelClass;

        // Generated UUID.
        $owner->uuid = (string) \Str::uuid();

        // Required name.
        $owner->name = $data['name'];

        // Set default meta because there is no default value in the model.
        $owner->meta = empty($data['meta']) ? [] : $data['meta'];

        return $owner;
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
