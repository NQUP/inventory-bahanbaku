<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function redirectToDashboard()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('manager')) {
            return redirect()->route('manager.dashboard');
        }
        if ($user->hasRole('gudang')) {
            return redirect()->route('gudang.dashboard');
        }
        if ($user->hasRole('supplier')) {
            return redirect()->route('supplier.dashboard');
        }
        if ($user->hasRole('pemesan')) {
            return redirect()->route('pemesanan.dashboard');
        }

        return redirect('/');
    }
}
