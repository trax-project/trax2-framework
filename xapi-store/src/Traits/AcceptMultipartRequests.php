<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Http\Request;
use Trax\XapiStore\Exceptions\XapiBadRequestException;
use Trax\XapiStore\HttpRequest;

trait AcceptMultipartRequests
{
    /**
     * Validate an multipart request.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return  array|false  Return the multipart content, or false if it is not a multipart.
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    protected function validateMultipartRequest(Request $request)
    {
        // No content type.
        if (!HttpRequest::hasHeader($request, 'Content-Type')) {
            throw new XapiBadRequestException('Missing Content-Type in request.');
        }
        
        // Check header.
        if (!HttpRequest::hasType($request, 'multipart/mixed')) {
            return false;
        }
       
        // Invalid content.
        $parts = $this->multiparts($request);
        if (empty($parts)) {
            throw new XapiBadRequestException('Invalid content in multipart request.');
        }

        return $parts;
    }

    /**
     * Return the parts of a multipart request.
     *
     * @param  \Illuminate\Http\Request  $request;
     * @return  array
     */
    public function multiparts(Request $request)
    {
        // Boundary not found.
        if (!$boundary = $this->multipartBoundary($request)) {
            return [];
        }

        $res = [];
        $crlf = "\r\n";
        $parts = explode('--'.$boundary.$crlf, HttpRequest::content($request));
        array_shift($parts);
        foreach ($parts as $part) {
            //
            // Parameters.
            $params = [];
            $sub = explode($crlf.$crlf, $part);
            if (count($sub) < 2) {
                continue;
            }
            $paramLines = explode($crlf, array_shift($sub));
            foreach ($paramLines as $line) {
                $split = explode(':', $line);
                if (count($split) < 2) {
                    continue;
                }
                $params[trim($split[0])] = trim($split[1]);
            }
            
            // Content.
            $content = implode($crlf.$crlf, $sub);
            $content = trim(str_replace($crlf.'--'.$boundary.'--', '', $content));
            
            // Result.
            $partRes = (object)array();
            if (isset($params['Content-Transfer-Encoding'])) {
                $partRes->encoding = $params['Content-Transfer-Encoding'];
            }
            if (isset($params['Content-Length'])) {
                $partRes->length = $params['Content-Length'];
            }
            if (isset($params['Content-Type'])) {
                $partRes->contentType = $params['Content-Type'];
            }
            if (isset($params['X-Experience-API-Hash'])) {
                $partRes->sha2 = $params['X-Experience-API-Hash'];
            }
            $partRes->content = $content;
            if (isset($partRes->sha2)) {
                $res[$partRes->sha2] = $partRes;
            } else {
                $res[] = $partRes;
            }
        }
        return $res;
    }

    /**
     * Return the multipart boundary.
     *
     * @param  \Illuminate\Http\Request  $request;
     * @return  string|false
     */
    protected function multipartBoundary(Request $request): string
    {
        $parts = explode("boundary=", HttpRequest::header($request, 'Content-Type'));
        if (count($parts) == 2) {
            $boundary = trim($parts[1], ' "');
            if (!empty($boundary)) {
                return $boundary;
            }
        }
        return false;
    }
}
