<?php

namespace Trax\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Trax\Repo\Contracts\CrudRepositoryContract;
use Trax\Repo\CrudRequest;
use Trax\Auth\Authorizer;
use Trax\Auth\Authentifier;

abstract class CrudController extends Controller
{
    use ControllerIncludesData, ControllerIncludesPaging;

    /**
     * @var \Trax\Auth\Authorizer
     */
    protected $authorizer;

    /**
     * @var \Trax\Auth\Authentifier
     */
    protected $authentifier;

    /**
     * The repository class.
     *
     * @var \Trax\Repo\Contracts\CrudRepositoryContract
     */
    protected $repository;              // You MUST define this.

    /**
     * The resource parameter name.
     *
     * @var string
     */
    protected $routeParameter;          // You MUST define this.

    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain;       // You SOULD define this if it differs from routeParameter.

    /**
     * Create the constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizer = app(Authorizer::class);
        $this->authentifier = app(Authentifier::class);

        if (!isset($this->permissionsDomain)) {
            $this->permissionsDomain = $this->routeParameter;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateRequest($request, true);
        $this->beforeRequest($crudRequest, $request);
        $this->beforeWrite($crudRequest, $request);
        $this->beforeStore($crudRequest, $request);
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Perform task.
        $resource = $this->repository->create($crudRequest->content());
        $responseData = $this->responseData($resource);
        return $this->responseWithIncludedData($responseData, $include);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateRequest($request, true);
        $this->beforeRequest($crudRequest, $request);
        $this->beforeWrite($crudRequest, $request);
        $this->beforeUpdate($crudRequest, $request);
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id);
        $this->authorizer->must($this->permissionsDomain . '.write', $resource);

        // Perform task.
        $resource = $this->repository->updateModel($resource, $crudRequest->content());
        $responseData = $this->responseData($resource);
        return $this->responseWithIncludedData($responseData, $include);
    }

    /**
     * Duplicate an existing resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateDuplicateRequest($request);
        $this->beforeRequest($crudRequest, $request);
        $this->beforeWrite($crudRequest, $request);
        $this->beforeDuplicate($crudRequest, $request);
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id);
        $this->authorizer->must($this->permissionsDomain . '.read', $resource);
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Perform task.
        $copy = $this->repository->duplicateModel($resource, $crudRequest->content());
        $responseData = $this->responseData($resource);
        return $this->responseWithIncludedData($responseData, $include);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateRequest($request);
        $this->beforeRequest($crudRequest, $request);
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id, $crudRequest->query());
        $this->authorizer->must($this->permissionsDomain . '.read', $resource);

        // Perform task.
        $responseData = $this->responseData($resource);
        return $this->responseWithIncludedData($responseData, $include);
    }

    /**
     * Remove the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id);
        $this->authorizer->must($this->permissionsDomain . '.delete', $resource);

        // Perform task.
        $this->repository->deleteModel($resource);
        return response('', 204);
    }

    /**
     * Remove the resources targetted by a query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyByQuery(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateRequest($request);
        $this->beforeRequest($crudRequest, $request);

        // Perform task.
        $scopeFilter = $this->authorizer->scopeFilter($this->permissionsDomain);
        if (!is_null($scopeFilter)) {
            $this->repository->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }
        return response('', 204);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateRequest($request);
        $this->beforeRequest($crudRequest, $request);
        $include = $this->validateIncludeRequest($request);

        // Perform task.
        $resources = $this->getResources($this->permissionsDomain, $this->repository, $crudRequest);

        $responseData = $this->responseData($resources);
        $this->addPagingData($responseData, $crudRequest);
        return $this->responseWithIncludedData($responseData, $include);
    }

    /**
     * Hook before any request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeRequest(CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
    }

    /**
     * Hook before a store request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeStore(CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
    }

    /**
     * Hook before an update request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeUpdate(CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
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
        // You may override this in your controller.
    }

    /**
     * Hook before a duplicate request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeDuplicate(CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
    }

    /**
     * Validate a store or update request on model.
     *
     * @param  \Illuminate\Http\Request  $request;
     * @param  bool  $withContent;
     * @return  \Trax\Repo\CrudRequest
     */
    protected function validateRequest(Request $request, bool $withContent = false): CrudRequest
    {
        $params = $request->validate(
            CrudRequest::validationRules()
        );
        $content = !$withContent ? null : $request->validate(
            $this->validationRules($request)
        );
        return new CrudRequest($params, $content);
    }

    /**
     * Validate a duplicate request on model.
     *
     * @param  \Illuminate\Http\Request  $request;
     * @return  \Trax\Repo\CrudRequest
     */
    protected function validateDuplicateRequest(Request $request): CrudRequest
    {
        $params = $request->validate(
            CrudRequest::validationRules()
        );
        $content = $request->validate(
            $this->duplicateValidationRules($request)
        );
        return new CrudRequest($params, $content);
    }

    /**
     * Get the validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    abstract protected function validationRules(Request $request);

    /**
     * Get the duplicate validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function duplicateValidationRules(Request $request)
    {
        return [];
    }

    /**
     * Get resources from a repository.
     *
     * @param  string  $domain
     * @param  \Trax\Repo\Contracts\CrudRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return \Illuminate\Support\Collection
     */
    protected function getResources(string $domain, CrudRepositoryContract $repository, CrudRequest $crudRequest = null)
    {
        $filter = $this->authorizer->scopeFilter($domain);
        if (is_null($filter)) {
            return collect([]);
        } elseif (isset($crudRequest)) {
            return $repository->addFilter($filter)->get($crudRequest->query());
        } else {
            return $repository->addFilter($filter)->get();
        }
    }

    /**
     * Count resources from a repository, limited to pagination when provided.
     *
     * @param  string  $domain
     * @param  \Trax\Repo\Contracts\CrudRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return int
     */
    protected function countResources(string $domain, CrudRepositoryContract $repository, CrudRequest $crudRequest = null): int
    {
        $filter = $this->authorizer->scopeFilter($domain);
        if (is_null($filter)) {
            return 0;
        } elseif (isset($crudRequest)) {
            return $repository->addFilter($filter)->count($crudRequest->query());
        } else {
            return $repository->addFilter($filter)->count();
        }
    }


    /**
     * Count all resources from a repository, without pagination params.
     *
     * @param  string  $domain
     * @param  \Trax\Repo\Contracts\CrudRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return int
     */
    protected function countAllResources(string $domain, CrudRepositoryContract $repository, CrudRequest $crudRequest = null): int
    {
        $filter = $this->authorizer->scopeFilter($domain);
        if (is_null($filter)) {
            return 0;
        } elseif (isset($crudRequest)) {
            return $repository->addFilter($filter)->countAll($crudRequest->query());
        } else {
            return $repository->addFilter($filter)->countAll();
        }
    }

    /**
     * Get response data.
     *
     * @param  mixed  $data
     * @return array
     */
    protected function responseData($data): array
    {
        if ($data instanceof Collection) {
            $data->transform(function ($model) {
                return $this->responseModel($model);
            });
        } else {
            $data = $this->responseModel($data);
        }
        return ['data' => $data];
    }

    /**
     * Hook to change the response model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    protected function responseModel($model)
    {
        return $model;
    }
}
