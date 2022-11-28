<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Http\Request;
use Trax\XapiStore\Traits\AcceptAlternateRequests;
use Trax\XapiStore\Traits\PreventUnkownInputs;
use Trax\XapiStore\Exceptions\XapiBadRequestException;
use Trax\XapiStore\Exceptions\XapiNoContentException;
use Trax\XapiStore\Exceptions\XapiAuthorizationException;
use Trax\XapiStore\HttpRequest;
use Trax\XapiStore\Stores\Statements\XapiStatementRequest;

trait XapiStatementValidation
{
    use AcceptAlternateRequests, PreventUnkownInputs, XapiStatementContentValidation;

    /**
     * Needed by the AcceptAlternateRequests trait.
     */
    protected $supportedAlternateMethods = ['GET', 'PUT'];

    /**
     * PUT rules.
     */
    protected $putRules = [
        'statementId' => 'required|uuid',
    ];

    /**
     * GET rules.
     */
    protected $getRules = [
        'agent' => 'xapi_agent',
        'verb' => 'iri',
        'activity' => 'iri',
        'registration' => 'uuid',
        'related_activities' => 'json_boolean',
        'related_agents' => 'json_boolean',
        'since' => 'iso_date',
        'until' => 'iso_date',
        'limit' => 'integer|min:0',
        'format' => 'xapi_format',
        'attachments' => 'json_boolean',
        'ascending' => 'json_boolean',
    ];

    /**
     * FIND rules.
     */
    protected $findRules = [
        'statementId' => 'uuid|forbidden_with:voidedStatementId',
        'voidedStatementId' => 'uuid|forbidden_with:statementId',
        'format' => 'xapi_format',
        'attachments' => 'json_boolean',
    ];

    /**
     * Validate a POST request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Stores\Statements\XapiStatementRequest
     */
    protected function validatePostRequest(Request $request)
    {
        // Validate content.
        list($statements, $attachments) = $this->validatePostRequestContent($request);

        // Prevent unknown inputs.
        $params = $this->preventUnkownInputs($request, []);

        // Return the request after contextual validation.
        $xapiRequest = new XapiStatementRequest($params, $statements, $attachments);
        return $xapiRequest->validate();
    }

    /**
     * Validate a PUT request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Stores\Statements\XapiStatementRequest
     */
    protected function validatePutRequest(Request $request)
    {
        // Validate content.
        list($statement, $attachments) = $this->validatePutRequestContent($request);

        // Validate rules.
        $this->validateRules($request, $this->putRules);
        
        // Prevent unknown inputs.
        $params = $this->preventUnkownInputs($request, array_merge(
            ['content'],
            array_keys($this->putRules),
            $this->alternateInputs($request)
        ));

        // Check unicity of the statementId input.
        $statementId = $request->input('statementId');
        if ($this->repository->findByUuid($statementId)) {
            throw new XapiNoContentException("A statement with UUID [$statementId] already exists.");
        }

        // Set the statement ID.
        $statement->id = $statementId;

        return new XapiStatementRequest($params, $statement, $attachments);
    }

    /**
     * Validate a GET request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Stores\Statements\XapiStatementRequest
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateGetRequest(Request $request)
    {
        try {
            // Validate rules.
            $this->validateRules($request, $this->getRules);
                    
            // Prevent unknown inputs.
            $params = $this->preventUnkownInputs($request, array_merge(
                ['after', 'before'],
                array_keys($this->getRules),
                $this->alternateInputs($request)
            ));

            // Don't forget the lang.
            $params['lang'] = HttpRequest::header($request, 'Accept-Language', 'en');
            //
        } catch (XapiBadRequestException $e) {
            //
            // Add Consistent-Through header.
            $e->addHeaders(
                ['X-Experience-API-Consistent-Through' => $this->repository->consistentThrough()]
            );
            throw $e;
        }

        // Return the request after contextual validation.
        $xapiRequest = new XapiStatementRequest($params);
        return $xapiRequest->validate();
    }

    /**
     * Validate a FIND request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Stores\Statements\XapiStatementRequest
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateFindRequest(Request $request)
    {
        try {
            // Validate rules.
            $this->validateRules($request, $this->findRules);

            // Prevent unknown inputs.
            $params = $this->preventUnkownInputs($request, array_merge(
                array_keys($this->findRules),
                $this->alternateInputs($request)
            ));

            // Don't forget the lang.
            $params['lang'] = HttpRequest::header($request, 'Accept-Language', 'en');
            //
        } catch (XapiBadRequestException $e) {
            //
            // Add Consistent-Through header.
            $e->addHeaders(
                ['X-Experience-API-Consistent-Through' => $this->repository->consistentThrough()]
            );
            throw $e;
        }

        // Return the request after contextual validation.
        $xapiRequest = new XapiStatementRequest($params);
        return $xapiRequest->validate();
    }

    /**
     * Validate a DELETE request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Stores\Statements\XapiStatementRequest
     *
     * @throws \Trax\XapiStore\Exceptions\XapiAuthorizationException
     */
    protected function validateDeleteRequest(Request $request)
    {
        throw new XapiAuthorizationException('DELETE request is not allowed on this API.');
    }
}
