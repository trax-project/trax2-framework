<?php

namespace Trax\Auth\Test\Utils;

use Trax\Auth\Stores\Owners\OwnerRepository;

class OwnerFactory extends Factory
{
    /**
     * Generate the required data to make a resource.
     *
     * @param array  $data
     * @param mixed  $model
     * @return array
     */
    public function requiredData(array $data = [], $model = null): array
    {
        if (isset($model)) {
            $required = [
                'name' => $model->name,
            ];
        } else {
            $required = [
                'name' => $this->faker->name,
            ];
        }
        return array_merge($required, $data);
    }

    /**
     * Make a resource instance.
     *
     * @param array  $data
     */
    public function make(array $data = [])
    {
        return $this->app->make(OwnerRepository::class)->create($this->data($data));
    }
}
