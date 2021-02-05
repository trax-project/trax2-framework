<?php

namespace Trax\Auth\Stores\BasicHttp;

use Trax\Repo\Contracts\ModelFactoryContract;

class BasicHttpFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return BasicHttp::class;
    }

    /**
     * Create a new model given model data.
     *
     * @return mixed
     */
    public static function make(array $data)
    {
        $modelClass = self::modelClass();
        $credentials = new $modelClass;

        // Required username.
        $credentials->username = $data['username'];

        // Required password.
        $credentials->password = $data['password'];

        return $credentials;
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
