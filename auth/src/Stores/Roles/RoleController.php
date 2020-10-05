<?php

namespace Trax\Auth\Stores\Roles;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\Auth\Stores\Owners\OwnerRepository;
use Trax\Core\Helpers as Trax;

class RoleController extends CrudController
{
    use HasOwner;

    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'role';

    /**
     * The owners repository.
     *
     * @var \Trax\Auth\Stores\Owners\OwnerRepository
     */
    protected $owners;

    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Stores\Roles\RoleRepository  $roles
     * @param  \Trax\Auth\Stores\Owners\OwnerRepository  $owners
     * @return void
     */
    public function __construct(RoleRepository $roles, OwnerRepository $owners)
    {
        parent::__construct();
        $this->repository = $roles;
        $this->owners = $owners;
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
            'description' => 'nullable|string',
            'permissions' => 'array',
            'meta' => 'array',
            'owner_id' => "nullable|integer|exists:trax_owners,id",
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
     * Get response complementary data.
     *
     * @param string  $name
     * @return mixed
     */
    protected function includeData(string $name)
    {
        switch ($name) {
            case 'owners':
                return Trax::select($this->getResources('owner', $this->owners));
            case 'permissions':
                return $this->authorizer->permissions('user');
        }
    }
}
