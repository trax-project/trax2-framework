<?php

namespace Trax\XapiStore\Stores\Agents;

use Illuminate\Http\Request;
use Trax\XapiStore\Traits\AcceptAlternateRequests;
use Trax\XapiStore\Traits\PreventUnkownInputs;
use Trax\XapiStore\Exceptions\XapiAuthorizationException;
use Trax\XapiStore\XapiRequest;

trait XapiAgentValidation
{
    use AcceptAlternateRequests, PreventUnkownInputs;

    /**
     * Needed by the AcceptAlternateRequests trait.
     */
    protected $supportedAlternateMethods = ['GET'];

    /**
     * GET rules.
     */
    protected $getRules = [
        'agent' => 'required|xapi_agent',
    ];

    /**
     * Validate a POST request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    protected function validatePostRequest(Request $request)
    {
        throw new XapiAuthorizationException('POST request is not allowed on this API.');
    }

    /**
     * Validate a PUT request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    protected function validatePutRequest(Request $request)
    {
        throw new XapiAuthorizationException('PUT request is not allowed on this API.');
    }

    /**
     * Validate a GET request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     */
    protected function validateGetRequest(Request $request)
    {
        // Validate rules.
        $this->validateRules($request, $this->getRules);
                
        // Prevent unknown inputs.
        $params = $this->preventUnkownInputs($request, array_merge(
            array_keys($this->getRules),
            $this->alternateInputs($request)
        ));

        // Return the request after contextual validation.
        $xapiRequest = new XapiRequest($params, null, 'agent', 'get');
        return $xapiRequest->validate();
    }

    /**
     * Validate a DELETE request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\XapiRequest
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    protected function validateDeleteRequest(Request $request)
    {
        throw new XapiAuthorizationException('DELETE request is not allowed on this API.');
    }
}
