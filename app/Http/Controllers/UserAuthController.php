<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class UserAuthController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($this->attemptLogin(Admin::class, $request)) {
            return redirect()->intended('admin/dashboard'); 
        }

        // If not found, check in the Customers table
        if ($this->attemptLogin(Customer::class, $request)) {
            return redirect()->intended('customer/reservation'); 
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    private function attemptLogin($model, $request)
    {
        $user = $model::where('email', $request->email)->first();

        if ($user && \Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return true;
        }

        return false;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
