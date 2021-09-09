<?php

namespace Trax\Repo\ModelAttributes;

/**
 * Eloquent models using this trait must define a boolean column named 'active'.
 */
trait ActivableModel
{
    /**
     * Initialize this trait.
     *
     * @return void
     */
    protected function initializeActivableModel()
    {
        $this->casts['active'] = 'boolean';
        $this->fillable[] = 'active';
        
        // Since a recent framework update, default value here override the loaded value!!!
        // So we move it to the attributes property of each model.
        // $this->attributes['active'] = true;
    }

    /**
     * Get the model activation.
     *
     * @return int
     */
    public function isActive(): int
    {
        return $this->active ? 1 : 0;
    }
}
