<?php

namespace Trax\Repo\ModelAttributes;

use Illuminate\Database\Eloquent\Model;

/**
 * Repositories using this trait must define models with:
 * - A boolean column named: active
 */
trait ActivableModelRepo
{
    /**
     * (De)Activate an existing resource.
     *
     * @param mixed  $id
     * @param bool  $activate
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function activate($id, bool $activate = true): Model
    {
        $resource = $this->findOrFail($id);
        $resource->active = $activate;
        $resource->save();
        return $resource;
    }
}
