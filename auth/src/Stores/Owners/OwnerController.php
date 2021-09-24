<?php

namespace Trax\Auth\Stores\Owners;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;
use Trax\Auth\Controllers\CrudController;
use Trax\Core\Helpers as Trax;

class OwnerController extends CrudController
{
    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter = 'owner';

    /**
     * Create the constructor.
     *
     * @param  \Trax\Auth\Stores\Owners\OwnerRepository  $owners
     * @return void
     */
    public function __construct(OwnerRepository $owners)
    {
        parent::__construct();
        $this->repository = $owners;
    }

    /**
     * Get the validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function validationRules(Request $request)
    {
        $unicity = $request->method() == 'POST' ? '' : ',' . $request->route('owner');
        return [
            'name' => "required|string|unique:trax_owners,name$unicity",
            'meta' => 'array',
        ];
    }

    /**
     * Hook before an index request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeIndex(CrudRequest $crudRequest, Request $request)
    {
        $crudRequest->addFilter(['deleted_at' => null]);
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
                return Trax::select($this->getResources('owner', $this->repository));
        }
    }
}
