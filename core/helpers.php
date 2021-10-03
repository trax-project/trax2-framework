<?php

if (!function_exists('traxAsset')) {
    /**
     * Override the Laravel asset() function in order to set the secure param
     * depending of the app config.
     *
     * @param  string  $path
     * @return string
     */
    function traxAsset($path)
    {
        return traxUrl($path);
    }
}

if (!function_exists('traxUrl')) {
    /**
     * Override the Laravel url() function in order to get a secure URL
     * depending of the app config.
     *
     * @param  string  $path
     * @return string
     */
    function traxUrl(string $path = '')
    {
        // Remove trailing slash on the base url.
        $base = config('app.url');
        if (\Str::of($base)->endsWith('/')) {
            $base = \Str::of($base)->beforeLast('/');
        }

        // Root without trailing slash.
        if (empty($path)) {
            return $base;
        }
        
        // Sub-url.
        return \Str::of($path)->startsWith('/')
            ? $base . $path
            : $base . '/' . $path;
    }
}

if (!function_exists('traxRequestUrl')) {
    /**
     * Override the Laravel $request->url() function in order to get a secure URL
     * depending of the app config.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    function traxRequestUrl(\Illuminate\Http\Request $request)
    {
        return traxUrl(
            \Str::of($request->url())->after(url(''))
        );
    }
}
