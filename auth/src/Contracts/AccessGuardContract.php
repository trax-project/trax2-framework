<?php

namespace Trax\Auth\Contracts;

use Illuminate\Http\Request;
use Trax\Repo\CrudRepository;

interface AccessGuardContract
{
    /**
     * Get the type used in the access model.
     *
     * @return string
     */
    public function type(): string;

    /**
     * Get the name of the guard for humans.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the guard credentials validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    public function validationRules(Request $request);

    /**
     * Get the credentials provider.
     *
     * @return \Trax\Repo\CrudRepository
     */
    public function provider(): CrudRepository;

    /**
     * Check the request credentials.
     *
     * @param  \Trax\Auth\Stores\BasicHttp\BasicHttp  $credentials
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function check($credentials, Request $request): bool;
}
