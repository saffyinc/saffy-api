<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user = $request->user();

        $user->update([
            'username' => $request->username ?? $user->username,
            'password' => $request->password ? Hash::make($request->string('password')) : $user->password,
        ]);

        // Refresh the in-memory authenticated user for this request
        Auth::guard('web')->setUser($user->fresh());

        return response()->noContent();
    }
}
