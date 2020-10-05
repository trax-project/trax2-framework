<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Http\Request;
use Trax\XapiStore\Traits\AcceptAlternateRequests;
use Trax\XapiStore\Traits\PreventUnkownInputs;
use Trax\XapiStore\HttpRequest;
use Trax\XapiStore\Exceptions\XapiBadRequestException;

/**
 * This trait can be used by controllers which extend the XapiController
 * and declare the following properties:
 * - $xapiRequestClass
 * - $putRules
 * - $getRules
 * - $deleteRules
 */
trait ValidateDocument
{
    use AcceptAlternateRequests, PreventUnkownInputs;

    /**
     * Needed by the AcceptAlternateRequests trait.
     *
     * @var array
     */
    protected $supportedAlternateMethods = ['GET', 'PUT', 'DELETE'];

    /**
     * Validate a POST request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Abstracts\XapiDocumentRequest
     */
    protected function validatePostRequest(Request $request)
    {
        return $this->validatePutRequest($request);
    }

    /**
     * Validate a PUT request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Abstracts\XapiDocumentRequest
     */
    protected function validatePutRequest(Request $request)
    {
        // Validate content.
        list($content, $type) = $this->validateContent($request);

        // Validate rules.
        $this->validateRules($request, $this->putRules);
        
        // Prevent unknown inputs.
        $params = $this->preventUnkownInputs($request, array_merge(
            ['content'],
            array_keys($this->putRules),
            $this->alternateInputs($request)
        ));

        $class = $this->xapiRequestClass;
        return new $class($params, $content, $type);
    }

    /**
     * Validate a GET request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Abstracts\XapiDocumentRequest
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

        $class = $this->xapiRequestClass;
        return new $class($params);
    }

    /**
     * Validate a DELETE request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return \Trax\XapiStore\Abstracts\XapiDocumentRequest
     */
    protected function validateDeleteRequest(Request $request)
    {
        // Validate rules.
        $this->validateRules($request, $this->deleteRules);
        
        // Prevent unknown inputs.
        $params = $this->preventUnkownInputs($request, array_merge(
            array_keys($this->deleteRules),
            $this->alternateInputs($request)
        ));

        $class = $this->xapiRequestClass;
        return new $class($params);
    }
    
    /**
     * Validate request content.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateContent(Request $request): array
    {
        if ($request->isJson()) {
            if (!$document = json_decode(HttpRequest::content($request), true)) {
                throw new XapiBadRequestException('Invalid JSON content.');
            }
            return [$document, 'application/json'];
        } elseif (HttpRequest::hasHeader($request, 'Content-Type')) {
            return [
                HttpRequest::content($request),
                HttpRequest::header($request, 'Content-Type')
            ];
        } else {
            throw new XapiBadRequestException('Missing Content-Type header.');
        }
    }
}
