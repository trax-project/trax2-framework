<?php

namespace Trax\XapiStore\Stores\StatementCategories;

use Trax\Repo\Contracts\ModelFactoryContract;

class StatementCategoryFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return StatementCategory::class;
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

        // Required IRI.
        $model->iri = $data['iri'];

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $model->owner_id = $data['owner_id'];
        }

        // Nullable profile.
        if (isset($data['profile'])) {
            $model->profile = $data['profile'];
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
