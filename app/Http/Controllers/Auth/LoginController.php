<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use ListensForLdapBindFailure;

    protected function handleLdapBindError()
    {
        throw ValidationException::withMessages([
            'email' => 'Whoops! LDAP server cannot be reached.',
        ]);
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Define username field for authentication.
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Override the username method to use 'username' instead of 'email
     */
    protected function credentials(Request $request)
    {
        return [
            'uid' => $request->username,
            'password' => $request->password,
            'fallback' => [
                'username' => $request->username,
                'password' => $request->password,
            ],
        ];
    }

    /**
     * Cek dulu apakah username ada di database sebelum LDAP login.
     */
    protected function attemptLogin(Request $request)
    {
        // Cek apakah username ada di tabel users
        $userExists = DB::table('users')->where('username', $request->username)->exists();

        if (! $userExists) {
            // Langsung throw error jika tidak ada
            throw ValidationException::withMessages([
                'username' => 'Username tidak ditemukan di sistem.',
            ]);
        }

        // Kalau ada, lanjut ke attempt login (LDAP)
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->listenForLdapBindFailure();
    }
}
