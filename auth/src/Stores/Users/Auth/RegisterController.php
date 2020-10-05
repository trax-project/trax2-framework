<?php

namespace Trax\Auth\Stores\Users\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Trax\Auth\Stores\Users\UserRepository;

class RegisterController extends Controller
{
    use RegistersUsers;

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
        $this->redirectTo = config('trax-auth.user.redirect.after_registration', '/home');
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // With Trax, the firstname and lastname must be provided.
        // The users table is defined dynamically.
        $userTable = $this->users->table();
        return Validator::make($data, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', "unique:$userTable"],
            'email' => ['required', 'string', 'email', 'max:255', "unique:$userTable"],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \Trax\Auth\Stores\Users\User
     */
    protected function create(array $data)
    {
        // Use the User factory for consistency.
        $user = $this->users->factory()::make($data);
        $user->save();
        return $user;
    }
}
