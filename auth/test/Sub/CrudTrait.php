<?php

namespace Trax\Auth\Test\Sub;

use Trax\Auth\Test\Utils\NoAuth;

trait CrudTrait
{
    use NoAuth;

    /**
     * Middlewares are deactivated to bypass authentication.
     * The authorizer is mocked.
     * We can focus on the HTTP CRUD operations.
     */
    protected function setUp(): void
    {
        parent::authSetup();
        $this->mockAuthorizer();
    }

    /**
     * Check that we get the right number of items.
     */
    public function testGetOne()
    {
        $resource = $this->api()->factory->make();

        $this->api()->find($resource->id)
            ->assertOk()
            ->assertJson(['data' => [
                'id' => $resource->id,
            ]]);
    }

    /**
     * Check that we get the right number of items.
     */
    public function testGetAll()
    {
        $count = count($this->api()->all()->json()['data']);

        $this->api()->factory->make();
        $this->api()->factory->make();

        $this->api()->all()
            ->assertOk()
            ->assertJsonCount($count + 2, 'data');
    }

    /**
     * Check that we get the right number of items after having deteted one of them.
     */
    public function testDelete()
    {
        $count = count($this->api()->all()->json()['data']);

        $first = $this->api()->factory->make();
        $last = $this->api()->factory->make();
        
        $this->api()->delete($last->id)
            ->assertNoContent();

        $this->api()->all()
            ->assertOk()
            ->assertJsonCount($count + 1, 'data');
        
        $this->api()->delete($first->id)
            ->assertNoContent();

        $this->api()->all()
            ->assertOk()
            ->assertJsonCount($count, 'data');
    }
}
