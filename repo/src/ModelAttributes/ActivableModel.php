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
        $this->attributes['active'] = true;
        $this->fillable[] = 'active';
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
