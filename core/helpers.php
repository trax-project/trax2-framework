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
        return asset($path, config('app.secure'));
    }
}

if (!function_exists('traxUrl')) {
    /**
     * Override the Laravel url() function in order to get a secure URL
     * depending of the app config.
     *
     * @param  string|null  $path
     * @param  mixed  $parameters
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    function traxUrl($path = null, $parameters = [])
    {
        return url($path, $parameters, config('app.secure'));
    }
}
