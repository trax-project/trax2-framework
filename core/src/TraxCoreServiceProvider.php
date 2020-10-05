<?php

namespace Trax\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Trax\Core\ExceptionHandler as TraxExceptionHandler;

class TraxCoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Exceptions handler.
        $this->app->singleton(ExceptionHandler::class, TraxExceptionHandler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Define validation rules.
        ValidationRules::register();
    }
}
