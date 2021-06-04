<?php

namespace Trax\XapiStore\Stores\States;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\XapiStore\Stores\States\StateRepository;

class StateController extends CrudController
{
    use HasOwner;
    
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'state';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\States\StateRepository  $repository
     * @return void
     */
    public function __construct(StateRepository $repository)
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
            'state_id' => 'required|string',
            'activity_id' => 'required|url',
            'agent' => 'required|xapi_agent',
            'registration' => 'nullable|uuid',
            'data.content' => 'required',
            'data.type' => 'required|content_type',
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
