<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     * @return string
     */
    protected function redirectTo()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return '/admin/dashboard';
            } elseif ($user->hasRole('manager')) {
                return '/manager/dashboard';
            } elseif ($user->hasRole('gudang')) {
                return '/gudang/dashboard';
            } elseif ($user->hasRole('supplier')) {
                return '/supplier/dashboard';
            } elseif ($user->hasRole('pemesan')) {
                return '/pemesanan/dashboard';
            }
        }

        // Fallback jika tidak ada role
        return '/home';
    }

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
