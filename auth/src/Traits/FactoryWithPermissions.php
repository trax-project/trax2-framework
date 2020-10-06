<?php

namespace Trax\Auth\Traits;

trait FactoryWithPermissions
{
    /**
     * Set model permissions.
     *
     * @param \Trax\Auth\Contracts\StorePermissionsContract  $model
     * @param array  $data
     */
    protected static function setModelPermissions($model, array &$data)
    {
        // Permissions may be an associative array or a simple array.
        // Default is set in the model.
        if (isset($data['permissions'])) {
            if (\Arr::isAssoc($data['permissions'])) {
                // Dots were replaces by `->`.
                // We need to restore dots.
                $values = array_values($data['permissions']);
                $keys = array_map(function ($key) {
                    return str_replace('->', '.', $key);
                }, array_keys($data['permissions']));
                $permissions = array_combine($keys, $values);

                $model->updateBooleanPermissions($permissions);
            } else {
                $model->updatePermissions($data['permissions']);
            }
            unset($data['permissions']);
        }
    }
}
