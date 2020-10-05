<?php

namespace Trax\Repo\ModelAttributes;

/**
 * Repositories using this trait must define models with:
 * - An UUID column named: uuid
 */
trait UuidModelRepo
{
    /**
     * Find an existing resource given its UUID.
     *
     * @param string  $uuid
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByUuid($uuid)
    {
        return $this->model()->where('uuid', $uuid)->first();
    }
}
