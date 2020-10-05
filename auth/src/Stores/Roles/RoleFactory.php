<?php

namespace Trax\Auth\Stores\Roles;

use Trax\Repo\Contracts\ModelFactoryContract;
use Trax\Auth\Traits\FactoryWithPermissions;

class RoleFactory implements ModelFactoryContract
{
    use FactoryWithPermissions;

    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Role::class;
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
        $role = new $modelClass;

        // Required name.
        $role->name = $data['name'];

        // Optional description.
        if (isset($data['description'])) {
            $role->description = $data['description'];
        }

        // Set permissions.
        self::setModelPermissions($role, $data);

        // Set default meta because there is no default value in the model.
        $role->meta = empty($data['meta']) ? [] : $data['meta'];

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $role->owner_id = $data['owner_id'];
        }

        return $role;
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
        self::setModelPermissions($model, $data);
        return $model;
    }
}
