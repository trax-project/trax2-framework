<?php

namespace Trax\Auth\Stores\Accesses;

use Trax\Repo\Contracts\ModelFactoryContract;
use Trax\Auth\Traits\FactoryWithPermissions;

class AccessFactory implements ModelFactoryContract
{
    use FactoryWithPermissions;

    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return Access::class;
    }

    /**
     * Create a new model given model data.
     *
     * @return mixed
     */
    public static function make(array $data)
    {
        $modelClass = self::modelClass();
        $access = new $modelClass;

        // Generated UUID.
        $access->uuid = (string) \Str::uuid();

        // Required client ID.
        $access->client_id = $data['client_id'];

        // Credentials.
        $access->credentials_id = $data['credentials_id'];
        $access->credentials_type = $data['credentials_type'];

        // Required name.
        $access->name = $data['name'];

        // Optional CORS.
        if (isset($data['cors'])) {
            $access->cors = $data['cors'];
        }

        // Optional boolean.
        if (isset($data['active'])) {
            $access->active = $data['active'];
        }

        // Optional boolean.
        if (isset($data['admin'])) {
            $access->admin = $data['admin'];
        }

        // Set permissions.
        self::setModelPermissions($access, $data);

        // Optional boolean.
        if (isset($data['inherited_permissions'])) {
            $access->inherited_permissions = $data['inherited_permissions'];
        }

        // Set default meta because there is no default value in the model.
        $access->meta = empty($data['meta']) ? [] : $data['meta'];

        return $access;
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
        // Empty but not null.
        if (array_key_exists('cors', $data) && is_null($data['cors'])) {
            $data['cors'] = '';
        }
        
        $model->update($data);
        self::setModelPermissions($model, $data);
        return $model;
    }
}
