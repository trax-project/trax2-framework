<?php

namespace Trax\XapiStore;

use Illuminate\Http\Request;

class HttpRequest
{
    /**
     * Check if the request has a given content type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $type
     * @return bool
     */
    public static function hasType(Request $request, string $type): bool
    {
        if (!self::hasHeader($request, 'Content-Type')) {
            return false;
        }
        $typeHeader = self::header($request, 'Content-Type');
        return strpos($typeHeader, $type) !== false;
    }

    /**
     * Check if a header exists, including with the alternate syntax.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $header
     * @return bool
     */
    public static function hasHeader(Request $request, string $header): bool
    {
        return !is_null(self::header($request, $header));
    }

    /**
     * Get a header, optionally with the alternate syntax.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $header
     * @param  mixed  $default
     * @return mixed
     */
    public static function header(Request $request, string $header, $default = null)
    {
        // Alternate request.
        if ($request->has('method') && $request->has($header)) {
            return $request->input($header, $default);
        }
        // Standard request.
        if ($request->hasHeader($header)) {
            return $request->header($header, $default);
        }
        return $default;
    }

    /**
     * Check if a content exists, including with the alternate syntax.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function hasContent(Request $request): bool
    {
        return !empty(self::content($request));
    }

    /**
     * Get content, optionally with the alternate syntax.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public static function content(Request $request)
    {
        if ($request->has('method') && $request->has('content')) {
            return urldecode($request->input('content'));
        } else {
            return $request->getContent();
        }
    }
}
