<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Http\Request;
use Trax\XapiStore\Exceptions\XapiBadRequestException;
use Trax\XapiStore\HttpRequest;

trait AcceptJsonRequests
{
    /**
     * Validate an JSON request and return the JSON object.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return  \stdClass|array
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateJsonRequest(Request $request)
    {
        // No content type.
        if (!HttpRequest::hasHeader($request, 'Content-Type')) {
            throw new XapiBadRequestException('Missing Content-Type in request.');
        }
        
        // Check header.
        if (!HttpRequest::hasType($request, 'application/json') &&
            !($request->has('method') && HttpRequest::hasType($request, 'application/x-www-form-urlencoded'))) {
                // No 'application/json' header.
                // Not an alternate request with a 'x-www-form-urlencoded' type.
                $type = HttpRequest::header($request, 'Content-Type');
                throw new XapiBadRequestException("Content-Type must be [application/json], not [$type].");
        }

        // No content.
        if (!HttpRequest::hasContent($request)) {
            throw new XapiBadRequestException('Missing JSON content in request.');
        }
            
        // Not a valid JSON content.
        $content = HttpRequest::content($request);
        if (!$json = json_decode($content)) {
            // We should accept empty arrays because some LRP may send empty batches.
            // See: https://github.com/trax-project/trax2-framework/issues/1
            if (!is_array($json)) {
                throw new XapiBadRequestException('Invalid JSON content in request.');
            }
        }

        return $json;
    }
}
