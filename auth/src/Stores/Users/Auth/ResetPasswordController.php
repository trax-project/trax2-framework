<?php

namespace Trax\Auth\Stores\Users\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Trax\Auth\Events\ResetDone;
use Trax\Auth\Events\ResetFailed;

class ResetPasswordController extends Controller
{
    use ResetsPasswords {
        credentials as nativeCredentials;
        sendResetResponse as nativeSendResetResponse;
        sendResetFailedResponse as nativeSendResetFailedResponse;
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
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|custom_password',
        ];
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

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        event(new ResetDone($request->input('email')));
        return $this->nativeSendResetResponse($request, $response);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        event(new ResetFailed($request->input('email')));
        return $this->nativeSendResetFailedResponse($request, $response);
    }
}
