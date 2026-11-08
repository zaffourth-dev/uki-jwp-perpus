<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function Login(Request $request)
    {
        $validated = $request->validate([
            "email" => "required|string|email",
            "password" => "required|string",
        ]);

        if (!Auth::attempt($validated)) {
            return back()->withErrors([
                "email" => "Email atau password tidak valid",
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route("dashboard"));
    }

    public function Logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login");
    }
}
