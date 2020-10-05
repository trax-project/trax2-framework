<?php

namespace Trax\Auth\Stores\BasicHttp;

use Illuminate\Http\Request;
use Trax\Auth\Contracts\AccessGuardContract;
use Trax\Repo\CrudRepository;

class BasicHttpGuard implements AccessGuardContract
{
    /**
     * The providers.
     *
     * @var \Trax\Auth\Stores\BasicHttp\BasicHttpProvider
     */
    protected $provider;

    /**
     * Get the type used in the access model.
     *
     * @return string
     */
    public function type(): string
    {
        return 'basic_http';
    }

    /**
     * Get the name of the guard for humans.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Basic HTTP';
    }

    /**
     * Get the guard credentials validation rules.
     *
     * @param \Illuminate\Http\Request  $request;
     * @return array
     */
    public function validationRules(Request $request)
    {
        return [
            'credentials.username' => 'required|alpha_dash',
            'credentials.password' => 'required|regex:/^\S*$/u',
        ];
    }

    /**
     * Get the credentials provider.
     *
     * @return \Trax\Repo\CrudRepository
     */
    public function provider(): CrudRepository
    {
        return $this->provider ?? $this->provider = new BasicHttpProvider();
    }

    /**
     * Check the request credentials.
     *
     * @param  \Trax\Auth\Stores\BasicHttp\BasicHttp  $credentials
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function check($credentials, Request $request): bool
    {
        // Get Authorization header.
        if ($request->hasHeader('Authorization')) {
            $authorization = $request->header('Authorization');
        } elseif ($request->has('method') && $request->has('Authorization')) {
            $authorization = $request->input('Authorization');
        } else {
            return false;
        }

        // Get credentials.
        list($basic, $auth) = explode(' ', $authorization);
        if ($basic != 'Basic') {
            return false;
        }
        list($username, $password) = explode(':', base64_decode(trim($auth)));
        if (empty($username)) {
            return false;
        }

        // Check credentials.
        if ($credentials->username != $username || $credentials->password != $password) {
            return false;
        }

        return true;
    }
}
