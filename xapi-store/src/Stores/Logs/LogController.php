<?php

namespace Trax\XapiStore\Stores\Logs;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\XapiStore\Stores\Logs\LogRepository;

class LogController extends CrudController
{
    use HasOwner;
    
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'log';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Logs\LogRepository  $repository
     * @return void
     */
    public function __construct(LogRepository $repository)
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
            'api' => 'required|in:statement,activity,agent,activity_profile,agent_profile,state',
            'method' => 'required|in:PUT,POST,GET,DELETE',
            'count' => 'nullable|integer',
            'error' => 'nullable|boolean',
            'data' => 'required|array',
            'voided' => 'boolean',
            'owner_id' => 'nullable|integer|exists:trax_owners,id',
            'entity_id' => 'nullable|integer|exists:trax_entities,id',
            'client_id' => 'nullable|integer|exists:trax_clients,id',
            'access_id' => 'nullable|integer|exists:trax_accesses,id',
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
