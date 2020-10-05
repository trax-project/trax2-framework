<?php

namespace Trax\Auth\Stores\Users\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords {
        credentials as nativeCredentials;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectTo = config('trax-auth.user.redirect.after_authentication', '/home');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetFormOrRedirect(Request $request, $token = null)
    {
        $redirect = config('trax-auth.user.redirect.password_reset', '/password/reset');
        
        // If there is no redirection, we display the Blade view.
        if ($redirect == '/password/reset') {
            return $this->showResetForm($request, $token);
        }

        // Else, we redirect.
        return redirect("$redirect/$token");
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        // With Trax, we must check that the user is active.
        return array_merge(
            $this->nativeCredentials($request),
            ['active' => true]
        );
    }
}
