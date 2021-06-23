<?php

namespace Trax\XapiStore\Stores\Activities;

use Illuminate\Http\Request;
use Trax\XapiStore\Abstracts\XapiController;
use Trax\XapiStore\Exceptions\XapiAuthorizationException;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Stores\Logs\Logger;

class XapiActivityController extends XapiController
{
    use XapiActivityValidation;
    
    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain = 'activity';

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
     * Post a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    public function post(Request $request)
    {
        // Alternate request.
        if ($redirectMethod = $this->checkAlternateRequest($request)) {
            return $this->$redirectMethod($request);
        }
        throw new XapiAuthorizationException('POST request is not allowed on this API.');
    }

    /**
     * Put a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    public function put(Request $request)
    {
        throw new XapiAuthorizationException('PUT request is not allowed on this API.');
    }

    /**
     * Get resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        // Validate request.
        $xapiRequest = $this->validateGetRequest($request);

        // Perform request.
        $resources = $this->getResources($xapiRequest);
        if (!$resource = $resources->last()) {
            // Return a minimal activity by default.
            $resource = (object)['data' => [
                'objectType' => 'Activity',
                'id' => $xapiRequest->param('activityId')]
            ];
        }

        // Logging.
        Logger::log($this->permissionsDomain, 'GET');

        // Response.
        return $this->response($resource->data);
    }

    /**
     * Delete a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    public function delete(Request $request)
    {
        throw new XapiAuthorizationException('DELETE request is not allowed on this API.');
    }
}
