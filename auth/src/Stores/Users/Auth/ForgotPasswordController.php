<?php

namespace Trax\Auth\Stores\Users\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Trax\Auth\Stores\Users\UserRepository;
use Trax\Auth\Events\ResetLinkSent;
use Trax\Auth\Events\ResetLinkFailed;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails {
        sendResetLinkResponse as nativeSendResetLinkResponse;
        sendResetLinkFailedResponse as nativeSendResetLinkFailedResponse;
    }

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
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $user = $this->users->addFilter(['email' => $request->input('email')])->get()->first();

        // The user does not exists. The request will fail later with these credentials.
        if (!$user) {
            return [
                'email' => $request->input('email'),
            ];
        }

        // The user account is not internal, we must emit a 403 error.
        if ($user->source != 'internal') {
            abort(403, "External accounts can't reset their password!");
        }

        // The user is an admin. It must be active and that's it.
        if ($user->admin) {
            return [
                'email' => $request->input('email'),
                'active' => true,
            ];
        }

        // Roles are not activated. It must be active and that's it.
        if (!config('trax-auth.services.roles', false)) {
            return [
                'email' => $request->input('email'),
                'active' => true,
            ];
        }

        // Roles are activated. It must be active and have a global role.
        return [
            'email' => $request->input('email'),
            'active' => true,
            'role_id' => is_null($user->role_id) ? 1 : $user->role_id,  // 1 will make the request fail.
        ];
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        event(new ResetLinkSent($request->input('email')));
        return $this->nativeSendResetLinkResponse($request, $response);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        event(new ResetLinkFailed($request->input('email')));
        return $this->nativeSendResetLinkFailedResponse($request, $response);
    }
}
