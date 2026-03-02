<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WebMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->canApprove()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|max:100',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput($request->only('login'));
        }

        $login = trim((string) $request->input('login'));

        $user = User::where('email', $login)
            ->orWhere('employee_id', $login)
            ->first();

        if (!$user || !$user->is_active || !Hash::check($request->input('password'), $user->password)) {
            return redirect()
                ->back()
                ->withErrors(['login' => WebMessages::AUTH_INVALID_CREDENTIALS])
                ->withInput($request->only('login'));
        }

        Auth::login($user, false);

        $request->session()->regenerate();

        if (!Auth::user()?->canApprove()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->back()
                ->withErrors(['login' => WebMessages::AUTH_NO_ADMIN_ACCESS]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
