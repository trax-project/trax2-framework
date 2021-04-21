<?php

namespace Trax\XapiStore\Stores\Agents;

use Illuminate\Http\Request;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Abstracts\XapiController;
use Trax\XapiStore\Exceptions\XapiAuthorizationException;
use Trax\XapiStore\Stores\Agents\AgentService;
use Trax\XapiStore\XapiLogging\XapiLogger;

class XapiAgentController extends XapiController
{
    use XapiAgentValidation;
    
    /**
     * The repository class.
     *
     * @var \Trax\XapiStore\Stores\Agents\AgentService
     */
    protected $repository;

    /**
     * The permissions domain.
     *
     * @var string
     */
    protected $permissionsDomain = 'agent';

    /**
     * Create the constructor.
     *
     * @param  \Trax\XapiStore\Stores\Agents\AgentService  $repository
     * @return void
     */
    public function __construct(AgentService $repository)
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
        if ($resource = $resources->last()) {
            // Return the matching Person.
            $person = $this->repository->getRealPerson($resource);
        } else {
            // We generate the result from the request param.
            $person = $this->repository->getVirtualPerson(
                json_decode($xapiRequest->param('agent'))
            );
        }

        // Logging.
        XapiLogger::log($this->permissionsDomain, 'GET');

        // Response.
        return $this->response($person);
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
