<?php

namespace Trax\Auth\Stores\Users\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Trax\Auth\Stores\Users\UserRepository;
use Trax\Auth\Events\LoggedIn;
use Trax\Auth\Events\LogInFailed;
use Trax\Auth\Events\LoggedOut;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        attemptLogin as nativeAttemptLogin;
        sendFailedLoginResponse as nativeSendFailedLoginResponse;
        logout as nativeLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo;

    /**
     * The users repository.
     *
     * @var \Trax\Auth\Stores\Users\UserRepository
     */
    protected $users;

    /**
     * Create a new controller instance.
     *
     * @param  \Trax\Auth\Stores\Users\UserRepository  $users
     *
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
        $this->redirectTo = config('trax-auth.user.redirect.after_authentication', '/home');
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        // Users are always authenticated on their username.
        // For email authentication, the client is responsible for providing identical email and username.
        return 'username';
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function attemptLogin(Request $request)
    {
        $user = $this->users->addFilter(['username' => $request->input('username')])->get()->first();

        // Now, we must check that the user is active.
        if (!$user || !$user->active) {
            throw new AuthenticationException();
        }

        // Then, if roles are activated, we check that the user has a role or is an admin.
        if (!$user->admin && config('trax-auth.services.roles', false) && !isset($user->role_id)) {
            throw new AuthenticationException();
        }
        
        // Finally, we can attempt to login.
        return $this->nativeAttemptLogin($request);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        event(new LoggedIn($user));
        return false;   // Don't return a specific response.
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        event(new LogInFailed($this->username()));
        return $this->nativeSendFailedLoginResponse($request);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = app(\Trax\Auth\Authentifier::class)->user();
        event(new LoggedOut($user));
        return $this->nativeLogout($request);
    }
}
