<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Trax\XapiStore\HttpRequest;
use Trax\XapiStore\Exceptions\XapiBadRequestException;
use Trax\XapiStore\Exceptions\XapiConflictException;
use Trax\XapiStore\Exceptions\XapiPreconditionFailedException;

trait ManageConcurrency
{
    /**
     * Validate concurrency.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  object|\Illuminate\Database\Eloquent\Model|false  $resource
     * @return void
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     * @throws \Trax\XapiStore\Exceptions\XapiConflictException
     * @throws \Trax\XapiStore\Exceptions\XapiPreconditionFailedException
     */
    protected function validateConcurrency(Request $request, $resource)
    {
        // If-Match.
        if (HttpRequest::hasHeader($request, 'If-Match')) {
            if (!$resource) {
                throw new XapiPreconditionFailedException('If-Match header does not match with the existing content.');
            } else {
                $content = is_string($resource->data->content) ?: json_encode($resource->data->content);
                
                // Remove the 'W/' which may be added by some servers or proxies:
                // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match
                // This should probably be solved by the client.
                $etag = HttpRequest::header($request, 'If-Match');
                if (Str::of($etag)->startsWith('W/')) {
                    $etag = Str::of($etag)->after('W/');
                }

                if ($etag != '"'.sha1($content).'"') {
                    throw new XapiPreconditionFailedException('If-Match header does not match with the existing content.');
                } else {
                    return;
                }
            }
        }
        
        // If-None-Match.
        if (HttpRequest::hasHeader($request, 'If-None-Match')) {
            if (HttpRequest::header($request, 'If-None-Match') != '*') {
                throw new XapiConflictException('Concurrency header If-None-Match must be *.');
            } elseif ($resource) {
                throw new XapiPreconditionFailedException('If-None-Match is set to * but there is an existing content.');
            } else {
                return;
            }
        }
        
        // Missing concurrency data.
        if ($resource) {
            throw new XapiConflictException('Missing concurrency header If-Match or If-None-Match.');
        }
        
        throw new XapiBadRequestException('Missing concurrency header If-Match or If-None-Match.');
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
        return $response->header('ETag', '"'.sha1($content).'"');
    }
}
