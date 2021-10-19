<?php

namespace Trax\Auth\Stores\Clients;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Stores\Owners\OwnerRepository;
use Trax\Auth\Stores\Entities\EntityRepository;
use Trax\Auth\Traits\HasOwner;
use Trax\Core\Helpers as Trax;

class ClientController extends CrudController
{
    use HasOwner;

    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'client';

    /**
     * The owners repository.
     *
     * @var \Trax\Auth\Stores\Owners\OwnerRepository
     */
    protected $owners;

    /**
     * The entities repository.
     *
     * @var \Trax\Auth\Stores\Entities\EntityRepository
     */
    protected $entities;

    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Stores\Clients\ClientRepository  $clients
     * @param  \Trax\Auth\Stores\Owners\OwnerRepository  $owners
     * @param  \Trax\Auth\Stores\Entities\EntityRepository  $entities
     * @return void
     */
    public function __construct(ClientRepository $clients, OwnerRepository $owners, EntityRepository $entities)
    {
        parent::__construct();
        $this->repository = $clients;
        $this->owners = $owners;
        $this->entities = $entities;
    }

    /**
     * Get the validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validationRules(Request $request)
    {
        return [
            'name' => "required|string",
            'active' => 'boolean',
            'admin' => 'boolean',
            'visible' => 'boolean',
            'category' => 'nullable|string',
            'permissions' => 'array',
            'entity_id' => "nullable|integer|exists:trax_entities,id",
            'owner_id' => "nullable|integer|exists:trax_owners,id",
            'meta' => 'array',
            'meta.authority.name' => "nullable|string|required_with:meta.authority.homePage",
            'meta.authority.homePage' => "nullable|url|required_with:meta.authority.name",
        ];
    }

    /**
     * Hook before a store or update request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeWrite(CrudRequest $crudRequest, Request $request)
    {
        $this->checkOwner($crudRequest);
    }

    /**
     * Remove the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Request $request)
    {
        // We can't delete our own account.
        // The consumer may be null during unit testing (e.g. CRUD tests).
        $consumer = $this->authentifier->consumer();
        if (isset($consumer) && !$this->authentifier->isUser()
            && $this->authentifier->consumer()->client->id == $request->route($this->routeParameter)
        ) {
            throw new AuthorizationException("Forbidden: you can't delete your own account.");
        }
        return parent::destroy($request);
    }

    /**
     * Get response complementary data.
     *
     * @param string  $name
     * @return mixed
     */
    protected function includeData(string $name)
    {
        switch ($name) {
            case 'permissions':
                return $this->authorizer->permissions('app');
            case 'owners':
                return Trax::select($this->getResources('owner', $this->owners));
            case 'entities':
                return Trax::select($this->getResources('entity', $this->entities));
        }
    }
}
