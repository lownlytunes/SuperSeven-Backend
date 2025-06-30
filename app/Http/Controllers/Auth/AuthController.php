<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginFormRequest;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    /**
     * Register a new user.
     *
     * @param RegisterFormRequest $request
     */
    public function register(RegisterFormRequest $request)
    {
        $request->validated();

        // Create the user
        $user = User::create([
            'first_name' => $request->first_name,
            'mid_name' => $request->mid_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_num' => $request->contact_no,
            'address' => $request->address,
        ]);

        // Create the customer record
        Customer::create([
            'user_id' => $user->id,
            'customer_type' => $request->customer_type,
        ]);

        return $this->sendResponse('User registered successfully.', new UserResource($user));
    }

    /**
     * Login the user.
     *
     * @param LoginFormRequest $request
     */
    public function login(LoginFormRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Check if user exists
        if (!$user = User::where('email', $credentials['email'])->first()) {
            return $this->sendError("This account doesn't exist.", 404);
        }

        // Implement rate limiting
        $attemptKey = 'failed-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($attemptKey, User::MAX_ATTEMPTS)) {
            return $this->sendError('Too many login attempts. Please try again later.', 429);
        }

        // Validate credentials
        $remember = $request->boolean('remember') ? true : false;
        if (!Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($attemptKey);

            return $this->sendError('Invalid credentials.', 401, [
                'X-Attempts' => RateLimiter::attempts($attemptKey),
                'X-Remaining-Attempts' => RateLimiter::remaining($attemptKey, User::MAX_ATTEMPTS),
            ]);
        }

        // Clear failed login attempts on successful login
        RateLimiter::clear($attemptKey);

        // Regenerate session
        $request->session()->regenerate();

        // Generate token for API authentication
        $token = $user->createToken('auth_token')->plainTextToken;

        // Check if the user is active
        if ($user->status !== 'active') {
            return $this->sendError('Your account is disabled contact support', 403);
        }

        return $this->sendResponse('User logged in successfully.', new UserResource($user), [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout the user.
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->user()->tokens()->delete();
        }

        return $this->sendResponse('Logged Out!');
    }

    /**
     * Get the authenticated user.
     *
     * @param Request $request
     */
    public function user(Request $request)
    {
        if (!auth()->user()) {
            return $this->sendError('Unauthenticated user.', 401);
        }

        $user = User::find(auth()->user()->id);

        $data = new UserResource($user);

        return $this->sendResponse('User retrieved successfully.', $data);
    }
}
