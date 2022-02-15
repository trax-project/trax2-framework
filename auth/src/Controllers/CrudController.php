<?php

namespace Trax\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Trax\Repo\Contracts\ReadableRepositoryContract;
use Trax\Repo\CrudRequest;
use Trax\Repo\Querying\Query;
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
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Hooks.
        $this->beforeRequest($crudRequest, $request);
        $this->beforeWrite($crudRequest, $request);
        $this->beforeStore($crudRequest, $request);

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
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id);
        $this->authorizer->must($this->permissionsDomain . '.write', $resource);

        // Hooks.
        $this->beforeRequest($crudRequest, $request);
        $this->beforeWrite($crudRequest, $request);
        $this->beforeUpdate($resource, $crudRequest, $request);

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
        $include = $this->validateIncludeRequest($request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id);
        $this->authorizer->must($this->permissionsDomain . '.read', $resource);
        $this->authorizer->must($this->permissionsDomain . '.write');

        // Hooks.
        $this->beforeRequest($crudRequest, $request);
        $this->beforeWrite($crudRequest, $request);
        $this->beforeDuplicate($resource, $crudRequest, $request);

        // Perform task.
        $copy = DB::transaction(function () use ($resource, $crudRequest) {
            return $this->repository->duplicateModel($resource, $crudRequest->content());
        });
        $responseData = $this->responseData($copy);
        return $this->responseWithIncludedData($responseData, $include);
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
        $include = $this->validateIncludeRequest($request);

        // Hooks.
        $this->beforeRequest($crudRequest, $request);
        $this->beforeIndex($crudRequest, $request);

        // Perform task.
        $resources = $this->getResources($this->permissionsDomain, $this->repository, $crudRequest);

        $responseData = $this->responseData($resources);
        $this->addPagingData($responseData, $crudRequest);
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
        $include = $this->validateIncludeRequest($request);

        // Hooks: do it now because they may affect the CRUD request.
        $this->beforeRequest($crudRequest, $request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id, $crudRequest->query());
        $this->authorizer->must($this->permissionsDomain . '.read', $resource);

        // Hooks: do it now because they may affect the CRUD request.
        $this->beforeShow($resource, $crudRequest, $request);

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
        // Validate request.
        $crudRequest = $this->validateRequest($request);

        // Check permissions.
        $id = $request->route($this->routeParameter);
        $resource = $this->repository->findOrFail($id);
        $this->authorizer->must($this->permissionsDomain . '.delete', $resource);

        // Hooks.
        $this->beforeRequest($crudRequest, $request);
        $this->beforeDestroy($resource, $crudRequest, $request);

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

        // Check permissions.
        $this->authorizer->must($this->permissionsDomain . '.delete');

        // Hooks.
        $this->beforeRequest($crudRequest, $request);
        $this->beforeDestroyByQuery($crudRequest, $request);

        // Perform task.
        $scopeFilter = $this->authorizer->scopeFilter($this->permissionsDomain);
        if (!is_null($scopeFilter)) {
            $this->repository->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }
        return response('', 204);
    }

    /**
     * Count resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function count(Request $request)
    {
        // Validate request.
        $crudRequest = $this->validateRequest($request);

        // Hooks.
        $this->beforeRequest($crudRequest, $request);

        // Perform task.
        $count = $this->countResources($this->permissionsDomain, $this->repository, $crudRequest);
        $data = ['count' => $count];
        if ($crudRequest->option('unfiltered')) {
            $crudRequest->removeFilters();
            $total = $this->countResources($this->permissionsDomain, $this->repository, $crudRequest);
            $data['unfiltered'] = $total;
        }
        return response()->json($data);
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
     * @param  \Illuminate\Database\Eloquent\Model
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeUpdate(Model $resource, CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
    }

    /**
     * Hook before a duplicate request.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeDuplicate(Model $resource, CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
    }

    /**
     * Hook before a show request.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeShow(Model $resource, CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
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
        // You may override this in your controller.
    }

    /**
     * Hook before a destroy request.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeDestroy(Model $resource, CrudRequest $crudRequest, Request $request)
    {
        // You may override this in your controller.
    }

    /**
     * Hook before a destroy by query request.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeDestroyByQuery(CrudRequest $crudRequest, Request $request)
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
     * @param  \Trax\Repo\Contracts\ReadableRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return \Illuminate\Support\Collection
     */
    protected function getResources(string $domain, ReadableRepositoryContract $repository, CrudRequest $crudRequest = null)
    {
        $scope = isset($crudRequest) ? $crudRequest->scope() : null;
        $filter = $this->authorizer->scopeFilter($domain, $scope);
        if (is_null($filter)) {
            return collect([]);
        } elseif (isset($crudRequest)) {
            return $repository->get(
                $crudRequest->query()->addFilter($filter)
            );
        } else {
            return $repository->get(
                (new Query)->addFilter($filter)
            );
        }
    }

    /**
     * Count resources from a repository, limited to pagination when provided.
     *
     * @param  string  $domain
     * @param  \Trax\Repo\Contracts\ReadableRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return int
     */
    protected function countResources(string $domain, ReadableRepositoryContract $repository, CrudRequest $crudRequest = null): int
    {
        $scope = isset($crudRequest) ? $crudRequest->scope() : null;
        $filter = $this->authorizer->scopeFilter($domain, $scope);
        if (is_null($filter)) {
            return 0;
        } elseif (isset($crudRequest)) {
            return $repository->count(
                $crudRequest->query()->addFilter($filter)
            );
        } else {
            return $repository->count(
                (new Query)->addFilter($filter)
            );
        }
    }

    /**
     * Count all resources from a repository, without pagination params.
     *
     * @param  string  $domain
     * @param  \Trax\Repo\Contracts\ReadableRepositoryContract  $repository
     * @param  \Trax\Repo\CrudRequest  $query
     * @return int
     */
    protected function countAllResources(string $domain, ReadableRepositoryContract $repository, CrudRequest $crudRequest = null): int
    {
        $scope = isset($crudRequest) ? $crudRequest->scope() : null;
        $filter = $this->authorizer->scopeFilter($domain, $scope);
        if (is_null($filter)) {
            return 0;
        } elseif (isset($crudRequest)) {
            return $repository->countAll(
                $crudRequest->query()->addFilter($filter)
            );
        } else {
            return $repository->countAll(
                (new Query)->addFilter($filter)
            );
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
