<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'personal_id'   => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        $user = User::create($validated);
        $user->sendEmailVerificationNotification(); // kur lidhet me frontend, ma ndryshe bohet
        $token = $user->createToken('auth_token', ['*'], now()->addMinutes(30))->plainTextToken;
        // $token = $user->createToken('auth_token', ['read-only'], now()->addMinutes(30))->plainTextToken;
        /**
         * Kur te lidhi me react ja ndrroj abilities te tokenit
         */

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . Str::lower($request->email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Keni dergu shum request-a. Ju lutem provoni pas ' . ceil($seconds / 60) . ' minuta(sh).'
            ], 429);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 300);
            return response()->json(['message' => 'Keni shenu diqka gabim'], 401);
        }

        RateLimiter::clear($key);
        $user  = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Ju lutem verifikoni veten permes email-it.'
            ], 403);
        }

        $token = $user->createToken('auth_token', ['write'], now()->addMinutes(30))->plainTextToken; // ktu supozohet qe user-i esht verifiku permes email-it

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Perdoruesi nuk u gjet'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email-i eshte verifikuar'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verifikimi u dergua ne email']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $key = 'login:' . Str::lower($request->email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Keni dergu shum request-a. Ju lutem provoni pas ' . ceil($seconds / 60) . ' minuta(sh).'
            ], 429);
        }
        RateLimiter::hit($key, 300);
        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));

        if ($status !== \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Nuk mundemi tju dergojme email'], 400);
        }

        return response()->json(['message' => 'Linku per ndryshim passwordi esht derguar ne email']);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => $password])
                    ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Reset token-i ka skaduar'], 400);
        }

        return response()->json(['message' => 'Password-i u ndrua me sukses']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => '\"Logged out\" me sukses']);
    }

    public function userTokenAbilities(Request $request)
    {
        $abilities = $request->user()->currentAccessToken()->abilities;

        return response()->json(
            ["abilities" => $abilities],
            200
        );
    }
}
