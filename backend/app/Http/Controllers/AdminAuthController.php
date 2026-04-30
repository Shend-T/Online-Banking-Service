<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Helpers\AuditLogger;

use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !\Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Email ose passwordi gabim!'], 401);
        }
        $token = $admin->createToken('admin_token', ['admin'])->plainTextToken; // abilities admin sepse nuk dojna admini me mujt me bo transaksione ne emer te ndonje personi tjeter

        // Duhet me rujt ne audit_logs
        AuditLogger::log(
            $admin->id,
            'admin login',
            null,
            $request->ip()
        );

        return response()->json([
            'admin' => $admin,
            'token' => $token,
        ]);
    }
    public function me(Request $request)
    {
        return response()->json(['admin' => $request->user()], 200);
    }
}
