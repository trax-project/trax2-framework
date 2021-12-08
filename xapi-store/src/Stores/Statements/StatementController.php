<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Repo\Contracts\ReadableRepositoryContract;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\Auth\Traits\HasEntity;
use Trax\XapiStore\Stores\Statements\StatementRepository;

class StatementController extends CrudController
{
    use HasOwner, HasEntity;
    
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'statement';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Statements\StatementRepository  $repository
     * @return void
     */
    public function __construct(StatementRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Get resources from a repository.
     *
     * @param  string  $domain
     * @param  \Trax\Repo\Contracts\ReadableRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return \Illuminate\Support\Collection
     */
    protected function getResources(string $domain, ReadableRepositoryContract $repository, CrudRequest $crudRequest = null)
    {
        $service = app(\Trax\XapiStore\Services\StatementRequest\StatementRequestService::class);
        return parent::getResources($domain, $service, $crudRequest);
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
            'data' => 'required|array',
            'voided' => 'boolean',
            'pending' => 'boolean',
            'validation' => 'nullable|in:-1,0,1',
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
        $this->checkEntity($crudRequest);
    }
}
