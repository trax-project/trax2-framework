<?php

namespace Trax\Auth\Test\Utils;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class TraxAuthTestCase extends TestCase
{
    use WithFaker;

    /**
     * Clients API.
     *
     * @var \Trax\Auth\Test\Utils\ResourceApi
     */
    public $clients;

    /**
     * Accesses API.
     *
     * @var \Trax\Auth\Test\Utils\ResourceApi
     */
    public $accesses;
    
    /**
     * Users API.
     *
     * @var \Trax\Auth\Test\Utils\ResourceApi
     */
    public $users;
    
    /**
     * Roles API.
     *
     * @var \Trax\Auth\Test\Utils\ResourceApi
     */
    public $roles;
    
    /**
     * Entities API.
     *
     * @var \Trax\Auth\Test\Utils\ResourceApi
     */
    public $entities;
    
    /**
     * Owners API.
     *
     * @var \Trax\Auth\Test\Utils\ResourceApi
     */
    public $owners;
    
    /**
     * Setup.
     *
     * @return  void
     */
    protected function authSetup($userAuth = false): void
    {
        putenv("TRAX_AUTH_IS_USER=" . ($userAuth ? 'true' : 'false'));
        parent::setUp();

        // Owners API.
        $this->owners = new ResourceApi('owners', new OwnerFactory($this->app, $this->faker), $this);

        // Roles API.
        $roleFactory = new RoleFactory($this->app, $this->faker);
        $roleFactory->setOwnerFactory($this->owners->factory);
        $this->roles = new ResourceApi('roles', $roleFactory, $this);

        // Entities API.
        $entityFactory = new EntityFactory($this->app, $this->faker);
        $entityFactory->setOwnerFactory($this->owners->factory);
        $this->entities = new ResourceApi('entities', $entityFactory, $this);

        // Clients API.
        $clientFactory = new ClientFactory($this->app, $this->faker);
        $clientFactory->setOwnerFactory($this->owners->factory);
        $clientFactory->setEntityFactory($this->entities->factory);
        $this->clients = new ResourceApi('clients', $clientFactory, $this);

        // Accesses API.
        $accessesFactory = new AccessFactory($this->app, $this->faker);
        $accessesFactory->setClientFactory($this->clients->factory);
        $this->accesses = new ResourceApi('accesses', $accessesFactory, $this);

        // Users API.
        $userFactory = new UserFactory($this->app, $this->faker);
        $userFactory->setOwnerFactory($this->owners->factory);
        $userFactory->setEntityFactory($this->entities->factory);
        $userFactory->setRoleFactory($this->roles->factory);
        $this->users = new ResourceApi('users', $userFactory, $this);
    }
}
