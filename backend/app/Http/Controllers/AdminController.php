<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Admin;
use App\Models\User;

class AdminController extends Controller
{
    public function getAllUsers(Request $request) {
        $users = User::with('accounts')->get();
        return response()->json(['users' => $users], 200);
    }

    // Per profen: Emrat e funksioneve dhe variablave po i lejme anglisht pasi qe po esht pak me leht me kodu kshtu, shpresoj qe nuk qet ndonje problem perdorimi i fjaleve anglisht :)
    public function getUserAccountTransactions(Request $request, int $id) {
        $user = User::where('id', $id)->with('accounts', 'accounts.transactions')->first();
        return response()->json(['user' => $user], 200);
    }
}
