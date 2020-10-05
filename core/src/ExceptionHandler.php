<?php

namespace Trax\Core;

use App\Exceptions\Handler;
use Illuminate\Database\QueryException;
use Throwable;

class ExceptionHandler extends Handler
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
        if ($exception instanceof QueryException) {
            return response($exception->getMessage(), 423);
        }

        return parent::render($request, $exception);
    }
}
