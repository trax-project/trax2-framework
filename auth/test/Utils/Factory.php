<?php

namespace Trax\Auth\Test\Utils;

use Illuminate\Contracts\Foundation\Application;
use Faker\Generator;

abstract class Factory
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application  $app
     * @param \Faker\Generator  $faker
     * @return void
     */
    public function __construct(Application $app, Generator $faker)
    {
        $this->app = $app;
        $this->faker = $faker;
    }
    
    /**
     * Get all required data to make a resource.
     *
     * @param array  $data
     * @param mixed  $model
     * @return array
     */
    public function data(array $data = [], $model = null): array
    {
        return array_merge($this->requiredData($data, $model), $data);
    }
    
    /**
     * Generate the required data to make a resource.
     *
     * @param array  $data
     * @param mixed  $model
     * @return array
     */
    abstract public function requiredData(array $data = [], $model = null): array;

    /**
     * Make a resource instance.
     *
     * @param array  $data
     */
    abstract public function make(array $data = []);
}
