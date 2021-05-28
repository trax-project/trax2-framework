<?php

namespace Trax\Auth\Stores\Users;

use Illuminate\Support\Facades\Hash;
use Trax\Repo\Contracts\ModelFactoryContract;
use Trax\Auth\Events\PasswordChanged;

class UserFactory implements ModelFactoryContract
{
    /**
     * Return the model class.
     *
     * @return string
     */
    public static function modelClass(): string
    {
        return config('auth.providers.users.model');
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
        $user = new $modelClass;

        // Generated UUID.
        $user->uuid = (string) \Str::uuid();

        // Required email and username (be it the same).
        $user->email = $data['email'];
        $user->username = $data['username'];

        // Encrypt password when provided!
        // In other cases, define a random password.
        $user->password = isset($data['password'])
            ? Hash::make($data['password'])
            : Hash::make(\Str::random(20));

        // Required firstname.
        $user->firstname = $data['firstname'];

        // Required lastname.
        $user->lastname = $data['lastname'];

        // Optional boolean.
        if (isset($data['active'])) {
            $user->active = $data['active'];
        }

        // Optional boolean.
        if (isset($data['admin'])) {
            $user->admin = $data['admin'];
        }

        // Optional source.
        if (isset($data['source'])) {
            $user->source = $data['source'];
        }

        // Set default meta because there is no default value in the model.
        $user->meta = empty($data['meta']) ? [] : $data['meta'];

        // Nullable role_id.
        if (isset($data['role_id'])) {
            $user->role_id = $data['role_id'];
        }

        // Nullable entity_id.
        if (isset($data['entity_id'])) {
            $user->entity_id = $data['entity_id'];
        }

        // Nullable owner_id.
        if (isset($data['owner_id'])) {
            $user->owner_id = $data['owner_id'];
        }

        return $user;
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
        $passwordChanged = false;

        // Encrypt password!
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $passwordChanged = true;
        }
        
        // Empty but not null.
        if (array_key_exists('password', $data) && is_null($data['password'])) {
            unset($data['password']);
        }

        $model->update($data);

        if ($passwordChanged) {
            event(new PasswordChanged($model));
        }

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
