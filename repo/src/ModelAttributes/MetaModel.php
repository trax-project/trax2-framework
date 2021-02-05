<?php

namespace Trax\Repo\ModelAttributes;

/**
 * Eloquent models using this trait must define a boolean column named 'admin'.
 */
trait MetaModel
{
    /**
     * Set a meta property.
     *
     * @param  string  $prop
     * @param  mixed  $value
     * @return void
     */
    public function setMeta(string $prop, $value): void
    {
        $meta = $this->meta;
        $meta[$prop] = $value;
        $this->meta = $meta;
    }

    /**
     * Get a meta property.
     *
     * @param  string  $prop
     * @return array|null
     */
    public function getMeta(string $prop)
    {
        return isset($this->meta[$prop])
            ? $this->meta[$prop]
            : null;
    }
}
