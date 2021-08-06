<?php

namespace Trax\XapiStore\Abstracts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Trax\Auth\Authorizer;
use Trax\XapiStore\Exceptions\XapiBadRequestException;
use Trax\XapiStore\Traits\AcceptAlternateRequests;

abstract class XapiController extends Controller
{
    use AcceptAlternateRequests;

    /**
     * @var \Trax\Auth\Authorizer
     */
    protected $authorizer;

    /**
     * The repository class.
     *
     * @var \Trax\Repo\Contracts\CrudRepositoryContract
     */
    protected $repository;              // You MUST define this.

    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain;       // You MUST define this.

    /**
     * Create the constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizer = app(Authorizer::class);
    }

    /**
     * Post a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    abstract public function post(Request $request);

    /**
     * Put a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    abstract public function put(Request $request);

    /**
     * Get resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    abstract public function get(Request $request);

    /**
     * Delete a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    abstract public function delete(Request $request);

    /**
     * Validate a POST request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     */
    abstract protected function validatePostRequest(Request $request);

    /**
     * Validate a PUT request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     */
    abstract protected function validatePutRequest(Request $request);

    /**
     * Validate a GET request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     */
    abstract protected function validateGetRequest(Request $request);

    /**
     * Validate a DELETE request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     */
    abstract protected function validateDeleteRequest(Request $request);

    /**
     * Check if the request is an alternate request, validate it, and return a redirection method when needed.
     *
     * @param  \Illuminate\Http\Request  $request;
     * @return  string|false  The redirection method or false.
     */
    protected function checkAlternateRequest(Request $request)
    {
        // Does not accept alternate requests.
        if (!isset($this->alternateInputs)) {
            return false;
        }
        
        // Validate the alternate request.
        $this->validateAlternateRequest($request);

        // Return the redirection method only when a redirection is needed.
        return $request->method() != $request->input('method') ? $request->input('method') : false;
    }

    /**
     * Validate the request rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @param array  $rules;
     * @return array
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateRules(Request $request, array $rules)
    {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            throw new XapiBadRequestException('One or more request inputs are not valid: '. "\n" . json_encode($e->errors()), $e->errors());
        }
    }

    /**
     * Get resources from a repository.
     *
     * @param  \Trax\XapiStore\XapiRequest  $xapiRequest
     * @param  string  $getMethod
     * @return  \Illuminate\Support\Collection
     */
    protected function getResources($xapiRequest, string $getMethod = 'get')
    {
        $filter = $this->authorizer->scopeFilter($this->permissionsDomain);
        if (is_null($filter)) {
            return collect([]);
        } else {
            return $this->repository->addFilter($filter)->$getMethod($xapiRequest->query());
        }
    }

    /**
     * Include data and get the JSON response.
     *
     * @param  mixed  $data
     * @return \Illuminate\Http\Response
     */
    protected function response($data)
    {
        return response()->json($data);
    }
}
