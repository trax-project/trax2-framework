<?php

namespace Trax\Auth\Test\Utils;

use Tests\TestCase;
use Trax\Auth\Test\Utils\Factory;

class ResourceApi
{
    /**
     * @var string
     */
    protected $api;

    /**
     * @var string
     */
    protected $project = 'trax';

    /**
     * @var \Trax\Auth\Test\Utils\Factory
     */
    public $factory;

    /**
     * @var \Tests\TestCase
     */
    protected $testCase;
    
    /**
     * Constructor.
     *
     * @param string  $api
     * @param \Trax\Auth\Test\Utils\Factory  $factory
     * @param \Tests\TestCase  $testCase
     * @param string  $project
     * @return void
     */
    public function __construct(string $api, Factory $factory, TestCase $testCase, string $project = 'trax')
    {
        $this->api = $api;
        $this->project = $project;
        $this->factory = $factory;
        $this->testCase = $testCase;
    }
    
    /**
     * Get all resources.
     *
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function all($consumer = null)
    {
        return $this->testCase($consumer)->getJson(
            $this->endpoint($consumer)
        );
    }

    /**
     * Get resources.
     *
     * @param array  $params
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function get(array $params = [], $consumer = null)
    {
        return $this->testCase($consumer)->json(
            'GET',
            $this->endpoint($consumer),
            $params
        );
    }

    /**
     * Delete a set of resource.
     *
     * @param array  $params
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function deleteByQuery(array $params = [], $consumer = null)
    {
        return $this->testCase($consumer)->deleteJson(
            $this->endpoint($consumer),
            $params
        );
    }

    /**
     * Get one resource.
     *
     * @param int  $id
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function find(int $id, $consumer = null)
    {
        return $this->testCase($consumer)->getJson(
            $this->endpoint($consumer, $id)
        );
    }

    /**
     * Delete a resource.
     *
     * @param int  $id
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function delete(int $id, $consumer = null)
    {
        return $this->testCase($consumer)->deleteJson(
            $this->endpoint($consumer, $id),
            []
        );
    }

    /**
     * Update a resource given some data to update.
     *
     * @param mixed  $model
     * @param array  $data
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function put($model, array $data = [], $consumer = null)
    {
        return $this->testCase($consumer)->putJson(
            $this->endpoint($consumer, $model->id),
            $this->factory->data($data, $model)
        );
    }

    /**
     * CReate a resource given some data.
     *
     * @param array  $data
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function post(array $data = [], $consumer = null)
    {
        return $this->testCase($consumer)->postJson(
            $this->endpoint($consumer),
            $this->factory->data($data)
        );
    }

    /**
     * Get the test case to perform requests.
     *
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return \Tests\TestCase
     */
    protected function testCase($consumer = null)
    {
        return $this->isUser($consumer)
            ? $this->testCase->actingAs($consumer)
            : $this->testCase->withHeaders($this->authHeaders($consumer));
    }

    /**
     * Get the basic auth headers given an access model.
     *
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return array
     */
    protected function authHeaders($consumer = null): array
    {
        return $this->isApp($consumer)
            ? [
                'Authorization' => 'Basic ' . base64_encode($consumer->credentials->username . ':' . $consumer->credentials->password),
                'X-Experience-API-Version' => '1.0.0'
            ]
            : [];
    }

    /**
     * Get the app endpoint given an access model.
     *
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @param int  $id
     * @return string
     */
    protected function endpoint($consumer = null, $id = null): string
    {
        return $this->isUser($consumer)
            ? $this->userEndpoint($id)
            : $this->appEndpoint($consumer, $id);
    }

    /**
     * Get the app endpoint given an access model.
     *
     * @param \Trax\Auth\Stores\Accesses\Access  $consumer
     * @param int  $id
     * @return string
     */
    protected function appEndpoint($access = null, $id = null): string
    {
        $base = isset($access)
            ? "/{$this->project}/api/{$access->uuid}/{$this->api}"
            : "/{$this->project}/api/513f4660-983f-4800-b3b4-5a52a29606da/{$this->api}";
        return isset($id) ? $base . '/' . $id : $base;
    }

    /**
     * Get the user endpoint.
     *
     * @param int  $id
     * @return string
     */
    protected function userEndpoint($id = null)
    {
        $base = "/{$this->project}/api/front/{$this->api}";
        return isset($id) ? $base . '/' . $id : $base;
    }

    /**
     * Is the consumer a user?.
     *
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return bool
     */
    protected function isUser($consumer = null)
    {
        return isset($consumer) && $consumer->isUser();
    }

    /**
     * Is the consumer an app?.
     *
     * @param \Trax\Auth\Contracts\ConsumerContract  $consumer
     * @return bool
     */
    protected function isApp($consumer = null)
    {
        return isset($consumer) && $consumer->isApp();
    }
}
