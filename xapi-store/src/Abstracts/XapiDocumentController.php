<?php

namespace Trax\XapiStore\Abstracts;

use Illuminate\Http\Request;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Exceptions\XapiNotFoundException;
use Trax\XapiStore\Stores\Logs\Logger;

abstract class XapiDocumentController extends XapiController
{
    /**
     * The repository class.
     *
     * @var \Trax\XapiStore\Contracts\DocumentRepositoryContract
     */
    protected $repository;              // You MUST define this.

    /**
     * Post a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function post(Request $request)
    {
        // Alternate request.
        if ($redirectMethod = $this->checkAlternateRequest($request)) {
            return $this->$redirectMethod($request);
        }

        // Validate request.
        $xapiRequest = $this->validatePostRequest($request);

        // Check permissions.
        // Perform.
        $resource = $this->getResources($xapiRequest)->last();
        if ($resource) {
            $this->authorizer->must($this->permissionsDomain . '.write', $resource);
            $this->repository->mergeModel($resource, $xapiRequest->data());
        } else {
            $this->authorizer->must($this->permissionsDomain . '.write');
            $this->createWithContext($xapiRequest->data());
        }

        // Logging.
        Logger::log($this->permissionsDomain, 'POST');

        // Response.
        return response('', 204);
    }

    /**
     * Put a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function put(Request $request)
    {
        // Validate request.
        $xapiRequest = $this->validatePutRequest($request);

        // Concurrency.
        $resource = $this->getResources($xapiRequest)->last();
        $this->validateConcurrency($request, $resource);

        // Check permissions.
        // Perform.
        if ($resource) {
            $this->authorizer->must($this->permissionsDomain . '.write', $resource);
            $this->repository->updateModel($resource, $xapiRequest->data());
        } else {
            $this->authorizer->must($this->permissionsDomain . '.write');
            $this->createWithContext($xapiRequest->data());
        }

        // Logging.
        Logger::log($this->permissionsDomain, 'PUT');

        // Response.
        return response('', 204);
    }

    /**
     * Get resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Trax\XapiStore\Exceptions\XapiNotFoundException
     */
    public function get(Request $request)
    {
        // Validate request.
        $xapiRequest = $this->validateGetRequest($request);

        // Perform request.
        $identifier = $this->xapiRequestClass::identifier();
        if ($xapiRequest->hasParam($identifier)) {
            // Single GET.
            if (!$resource = $this->getResources($xapiRequest)->last()) {
                throw new XapiNotFoundException();
            }

            // Logging.
            Logger::log($this->permissionsDomain, 'GET');

            return $this->response($resource->data->content, $resource->data->type);
        } else {
            // Multiple GET.
            $content = $this->getResources($xapiRequest)->pluck(\Str::snake($identifier))->all();

            // Logging.
            Logger::log($this->permissionsDomain, 'GET');

            return $this->response($content);
        }
    }

    /**
     * Delete a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Validate request.
        $xapiRequest = $this->validateDeleteRequest($request);

        // Check permissions.
        $resources = $this->getResources($xapiRequest);
        $resource = $resources->first();
        $this->authorizer->must($this->permissionsDomain . '.delete', $resource);

        // Perform request.
        $this->repository->deleteModels($resources);

        // Logging.
        Logger::log($this->permissionsDomain, 'DELETE');

        // Response.
        return response('', 204);
    }

    /**
     * Create a resource.
     *
     * @param  array  $data
     * @return void
     */
    protected function createWithContext(array $data)
    {
        $data = array_merge($data, TraxAuth::context());
        $this->repository->create($data);
    }

    /**
     * Get the JSON response.
     *
     * @param  mixed  $content
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    protected function response($content, $type = 'application/json')
    {
        if ($type == 'application/json') {
            $response = response()->json($content);
            $content = json_encode($content);  // For the next step
        } else {
            $response = response($content, 200)
                        ->header('Content-Type', $type)
                        ->header('Content-Length', mb_strlen($content, '8bit'));
        }
        return $this->concurrencyResponse($response, $content);
    }

    /**
     * Validate concurrency.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \stdClass|\Illuminate\Database\Eloquent\Model  $resource
     * @return void
     */
    protected function validateConcurrency(Request $request, $resource)
    {
        // This may be overrided by the ManageConcurrency trait.
    }

    /**
     * Get a concurrency compliant response.
     *
     * @param  \Illuminate\Http\Response  $response
     * @param  string  $content
     * @return \Illuminate\Http\Response
     */
    protected function concurrencyResponse($response, string $content)
    {
        // This may be overrided by the ManageConcurrency trait.
        return $response;
    }
}
