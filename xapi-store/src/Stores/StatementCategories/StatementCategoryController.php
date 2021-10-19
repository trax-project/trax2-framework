<?php

namespace Trax\XapiStore\Stores\StatementCategories;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Auth\Traits\HasOwner;
use Trax\XapiStore\Stores\StatementCategories\StatementCategoryRepository;

class StatementCategoryController extends CrudController
{
    use HasOwner;
    
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'statement_category';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\StatementCategories\StatementCategoryRepository  $repository
     * @return void
     */
    public function __construct(StatementCategoryRepository $repository)
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
            'iri' => 'required|url',
            'owner_id' => 'nullable|integer|exists:trax_owners,id',
            'profile' => 'nullable|boolean',
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
