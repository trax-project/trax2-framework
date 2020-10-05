<?php

namespace Trax\XapiValidation;

use Illuminate\Support\ServiceProvider;

class XapiValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Define validation rules.
        XapiValidationRules::register();
    }
}
