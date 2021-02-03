<?php

namespace Trax\Auth\Stores\Users\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Trax\Auth\Stores\Users\UserRepository;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

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

        // If roles are activated, we check that the user has a role or is an admin.
        if ($user && !$user->admin && config('trax-auth.services.roles', false) && !isset($user->role_id)) {
            // We want the search to fail. And sure, it will fail.
            $role_id = 1;
        } else {
            // We want the search to succeed.
            $role_id = $user->role_id;
        }

        return [
            'email' => $request->input('email'),
            'active' => true,
            'role_id' => $role_id
        ];
    }
}
