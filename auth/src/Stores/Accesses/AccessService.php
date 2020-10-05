<?php

namespace Trax\Auth\Stores\Accesses;

use Illuminate\Support\Facades\DB;
use Trax\Auth\TraxAuth;

class AccessService extends AccessRepository
{
    /**
     * Create a new resource.
     *
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Create credentials.
            $credentialsFactory = TraxAuth::guard($data['type'])->provider()->factory();
            $credentials = $credentialsFactory::make($data['credentials']);
            $credentials->save();

            // Pass references to access.
            $data['credentials_id'] = $credentials->id;
            $data['credentials_type'] = $credentialsFactory::modelClass();

            return parent::create($data);
        });
    }

    /**
     * Update an existing resource, given its model and new data.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateModel($model, array $data = null)
    {
        return DB::transaction(function () use ($model, $data) {

            // Update credentials.
            $credentialsFactory = TraxAuth::guard($model->type)->provider()->factory();
            $credentials = $credentialsFactory::update($model->credentials, $data['credentials']);
            $credentials->save();

            return parent::updateModel($model, $data);
        });
    }

    /**
     * Delete an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleteModel($model)
    {
        return DB::transaction(function () use ($model) {

            // Destroy credentials.
            $provider = TraxAuth::guard($model->type)->provider();
            $provider->deleteModel($model->credentials);

            return parent::deleteModel($model);
        });
    }

    /**
     * deleteModels, deleteByQuery and truncate should also be overriden.
     * However, they are not used in our CRUD management.
     */
}
