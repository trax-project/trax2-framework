<?php

namespace Trax\XapiStore\Stores\Activities;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\XapiStore\Stores\Activities\ActivityRepository;

class ActivityController extends CrudController
{
    use HasOwner;
    
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'activity';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Activities\ActivityRepository  $repository
     * @return void
     */
    public function __construct(ActivityRepository $repository)
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
            'data' => 'required|array',
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
