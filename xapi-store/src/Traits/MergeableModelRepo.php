<?php

namespace Trax\XapiStore\Traits;

trait MergeableModelRepo
{
    /**
     * Merge data with an existing model.
     *
     * @param  \Illuminate\Database\Eloquent\Model|object  $resource
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function mergeModel($resource, array $data)
    {
        if ($this->factory()::merge($resource, $data)) {
            return $this->updateModel($resource);
        } else {
            return $resource;
        }
    }

    /**
     * Merge multiple data with an existing model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @param array  $bulk
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function mergeModelWithMultipleData($resource, array $bulk)
    {
        $changes = false;
        foreach ($bulk as $data) {
            if (!is_array($data)) {
                $data = json_decode(json_encode($data), true);
            }
            $changes = $this->factory()::merge($resource, $data) || $changes;
        }
        if ($changes) {
            return $this->updateModel($resource);
        } else {
            return $resource;
        }
    }
}
