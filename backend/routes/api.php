<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{user}', [UserController::class, 'show']);
Route::post('/user', [UserController::class, 'store']);
Route::put('/user/{user}', [UserController::class, 'update']);
Route::delete('/user/{user}', [UserController::class, 'destroy']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
// Route::post('/auth/login', [AuthController::class, 'login'])->middleware("throttle:5,15");

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/abilities', [AuthController::class, 'userTokenAbilities']);
});

Route::post('/email/resend', [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return response()->json(['message' => 'Linku gabim!'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email-i ka qene i verifikuar']);
    }

    $user->markEmailAsVerified();

    $user->tokens()->delete();

    // $newToken = $user->createToken('access_token', ['*'], now()->addMinutes(30))->plainTextToken;

    // return response()->json([
    //     'message' => 'Email-i u verifiku me sukses',
    //     'token'   => $newToken
    // ]);
    /**
     * Kur te lidhi me react ndrron qekjo
     */

    return response()->json(['message' => 'Email-i u verifiku me sukses']);
})->middleware('signed')->name('verification.verify');

Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::post('/auth/admin', [AdminAuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/me', [AdminAuthController::class, 'me']);

    Route::get('/users', [AdminController::class, 'getAllUsers']);
    Route::get('/user/{id}', [AdminController::class, 'getUserAccountTransactions']);
});