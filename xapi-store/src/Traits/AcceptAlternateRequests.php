<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Http\Request;
use Trax\XapiStore\Exceptions\XapiBadRequestException;

/**
 * This trait can be used by controllers in order to manage xAPI alternate requests.
 * The following properties must be defined in the using classes:
 * - protected $supportedAlternateMethods = ['get', 'post'];
 */
trait AcceptAlternateRequests
{
    /**
     * Accepted additional inputs for alternate requests.
     *
     * @var array
     */
    protected $alternateInputs = [
        'method',
        'Accept',
        'Accept-Encoding',
        'Accept-Language',
        'Authorization',
        'Content-Type',
        'Content-Length',
        'Content-Transfer-Encoding',
        'X-Experience-API-Version',
        'If-Match',
        'If-None-Match',
    ];

    /**
     * Validate an alternate request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return void
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateAlternateRequest(Request $request)
    {
        // Not an alternate request.
        if (!$request->has('method')) {
            return;
        }

        // Only POST requests.
        $method = $request->method();
        if ($method != 'POST') {
            throw new XapiBadRequestException("Alternate requests must use the POST method, not the [$method] method.");
        }
            
        // Check that there is only the 'method' param in the query string.
        $query = $request->query();
        if (count($query) > 1 || !isset($query['method'])) {
            unset($query['method']);
            $params = json_encode(array_keys($query));
            throw new XapiBadRequestException("The $params param(s) is (are) not supported in the query string of an alternate request.");
        }

        // Unsupported HTTP method.
        $method = strtoupper($request->input('method'));
        if (!isset($this->supportedAlternateMethods) || !in_array($method, $this->supportedAlternateMethods)) {
            throw new XapiBadRequestException("The [$method] alternate method is not supported on this API.");
        }
    }

    /**
     * Get the alternate inputs.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    protected function alternateInputs(Request $request)
    {
        return $request->has('method') ? $this->alternateInputs : [];
    }
}
