<?php

namespace Trax\XapiStore\Exceptions;

use Trax\Core\ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;
use Trax\XapiValidation\Exceptions\XapiValidationException;

class XapiExceptionHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // xAPI exceptions.
        if ($exception instanceof XapiBadRequestException
            || $exception instanceof XapiAuthorizationException
            || $exception instanceof XapiNotFoundException
            || $exception instanceof XapiConflictException
            || $exception instanceof XapiPreconditionFailedException
            || $exception instanceof XapiNoContentException
            || $exception instanceof XapiValidationException
        ) {
            return response(
                $exception->getMessage(),
                $exception->status(),
                $exception->headers()
            );
        }

        // Not in the context of an xAPI request.
        if (!$request->hasHeader('X-Experience-API-Version')) {
            return parent::render($request, $exception);
        }

        // Other exceptions.
        if ($exception instanceof AuthenticationException) {
            return response($exception->getMessage(), 401);
        }
        if ($exception instanceof AuthorizationException) {
            return response($exception->getMessage(), 403);
        }
        return response($exception->getMessage(), 400);
    }
}
