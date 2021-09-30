<?php

namespace Trax\Auth\Stores\Accesses;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Stores\Clients\ClientRepository;
use Trax\Core\Helpers as Trax;

class AccessController extends CrudController
{
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'access';

    /**
     * The clients repository.
     *
     * @var \Trax\Auth\Stores\Clients\ClientRepository
     */
    protected $clients;

    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Stores\Accesses\AccessService  $repository
     * @param  \Trax\Auth\Stores\Clients\ClientRepository  $clients
     * @return void
     */
    public function __construct(AccessService $repository, ClientRepository $clients)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->clients = $clients;
    }

    /**
     * Get the validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validationRules(Request $request)
    {
        // Get the credentials validation rules.
        // The 'type' input must be provided or an error will be rised.
        $guard = $this->authentifier->guard($request->input('type'));

        // Return merged rules.
        return array_merge([
            'client_id' => 'required|integer|exists:trax_clients,id',
            'type' => 'required|string',
            'name' => 'required|string',
            'cors' => 'nullable|string',
            'active' => 'boolean',
            'admin' => 'boolean',
            'visible' => 'boolean',
            'permissions' => 'array',
            'inherited_permissions' => 'boolean',
            'meta' => 'array',
        ], $guard->validationRules($request));
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
     * Check the owner.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function checkOwner(CrudRequest $crudRequest)
    {
        // The consumer may be null during unit testing (e.g. CRUD tests).
        $consumer = $this->authentifier->consumer();
        if (!isset($consumer)) {
            return;
        }

        // Consumers with no owner do what they want.
        if (empty($consumer->owner_id)) {
            return;
        }

        // Others can only create accesses for clients with the same owner_id.
        $client = $this->clients->findOrFail($crudRequest->contentField('client_id'));
        if ($consumer->owner_id != $client->owner_id) {
            throw new AuthorizationException("Forbidden: you can't create an access for a client you don't own.");
        }
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
            && $this->authentifier->consumer()->id == $request->route($this->routeParameter)
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
            case 'clients':
                return Trax::select($this->getResources('client', $this->clients));
            case 'permissions':
                return $this->authorizer->permissions('app');
            case 'guards':
                return $this->authentifier->guardsSelect();
        }
    }
}
