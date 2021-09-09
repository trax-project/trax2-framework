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
        $this->fillable[] = 'admin';
        
        // Since a recent framework update, default value here override the loaded value!!!
        // So we move it to the attributes property of each model.
        // $this->attributes['admin'] = false;
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
