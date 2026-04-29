<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\Rules\Password;

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
        $token = $user->createToken('auth_token')->plainTextToken;

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

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Keni shenu diqka gabim'], 401);
        }

        $user  = Auth::user();


        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Ju lutem verifikoni veten permes email-it.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
