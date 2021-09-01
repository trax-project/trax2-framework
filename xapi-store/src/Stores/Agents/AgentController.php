<?php

namespace Trax\XapiStore\Stores\Agents;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\XapiStore\Stores\Agents\AgentRepository;

class AgentController extends CrudController
{
    use HasOwner;
    
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'agent';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Agents\AgentRepository  $repository
     * @return void
     */
    public function __construct(AgentRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
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
            'agent' => 'required|array',
            'person_id' => 'required|integer|exists:trax_xapi_persons,id',
            'pseudonymized' => 'boolean',
            'owner_id' => 'nullable|integer|exists:trax_owners,id',
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
}
