<?php

namespace Trax\Auth\Test\Utils;

use Trax\Auth\Stores\Accesses\AccessService;

class AccessFactory extends Factory
{
    /**
     * @var \Trax\Auth\Test\Utils\ClientFactory
     */
    protected $clientFactory;

    /**
     * Set the client factory because it is needed.
     *
     * @param \Trax\Auth\Test\Utils\ClientFactory  $clientFactory
     * @return void
     */
    public function setClientFactory(ClientFactory $clientFactory): void
    {
        $this->clientFactory = $clientFactory;
    }

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
                'client_id' => $model->client_id,
                'name' => $model->name,
                'type' => 'basic_http',
                'credentials' => [
                    'username' => $model->credentials->username,
                    'password' => $model->credentials->password,
                ]
            ];
        } else {
            $required = [
                'name' => $this->faker->name,
                'type' => 'basic_http',
                'credentials' => [
                    'username' => \Str::random(10),
                    'password' => \Str::random(10),
                ]
            ];

            // Do we need a client?
            if (!isset($data['client_id'])) {
                $clientData = isset($data['client']) ? $data['client'] : [];
                $client = $this->clientFactory->make($clientData);
                $required['client_id'] = $client->id;
            }
            unset($data['client']);
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
        return $this->app->make(AccessService::class)->create($this->data($data));
    }

    /**
     * Make an admin access.
     */
    public function makeAdmin()
    {
        return $this->app->make(AccessService::class)->create($this->data([
            'client' => ['admin' => true],
        ]));
    }
}
