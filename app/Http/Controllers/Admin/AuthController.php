<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()?->is_admin) {
                Auth::logout();

                return back()->withErrors([
                    'email' => __('admin.auth.no_admin_rights'),
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', __('admin.auth.welcome_flash'));
        }

        return back()->withErrors([
            'email' => __('admin.auth.invalid_credentials'),
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $locale = $request->session()->get('admin_locale', 'uz');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('admin_locale', $locale);

        return redirect()->route('admin.login');
    }
}
