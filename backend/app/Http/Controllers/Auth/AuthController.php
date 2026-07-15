<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login endpoint with rate limiting
     */
    public function login(Request $request)
    {
        // Rate limiting: 5 attempts per minute per email
        $throttleKey = 'login-attempt:' . $request->ip() . ':' . $request->email;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . RateLimiter::availableIn($throttleKey) . ' seconds.'],
            ]);
        }

        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:255',
            'domain' => 'required|string|max:255|regex:/^[a-zA-Z0-9.-]+$/', // Validate domain format
        ]);

        $tenant = Tenant::where('domain', $request->domain)
            ->first();

        if (!$tenant || !$tenant->is_active) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'domain' => ['Invalid or inactive tenant domain.'],
            ]);
        }

        // Eager load role to prevent N+1 queries
        $user = User::with('role')
            ->where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Clear rate limit on successful login
        RateLimiter::clear($throttleKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->slug ?? null,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
            ],
        ]);
    }

    /**
     * Logout endpoint
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('role', 'tenant');
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->slug ?? null,
            'tenant' => $user->tenant,
        ]);
    }
}
