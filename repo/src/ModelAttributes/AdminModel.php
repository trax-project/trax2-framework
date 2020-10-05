<?php

namespace Trax\Repo\ModelAttributes;

/**
 * Eloquent models using this trait must define a boolean column named 'admin'.
 */
trait AdminModel
{
    /**
     * Initialize this trait.
     *
     * @return void
     */
    protected function initializeAdminModel()
    {
        $this->casts['admin'] = 'boolean';
        $this->attributes['admin'] = false;
        $this->fillable[] = 'admin';
    }

    /**
     * Check if a consumer is an admin and have all permissions.
     *
     * @return int
     */
    public function isAdmin(): int
    {
        return $this->admin ? 1 : 0;
    }
}
