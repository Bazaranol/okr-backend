<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fullName' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('fullName')) {
            $user->fullName = $request->fullName;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Profile was updated'], 200);
    }

    public function get(Request $request) {
        $user = Auth::user()->load('roles'); // Eager Loading

        $roles = $user->roles()->pluck('name');

        $group = Group::where('id', $user->group_id)->first();
        return response()->json([
            'fullName' => $user->fullName,
            'email' => $user->email,
            'roles' => $roles,
            'group_name' => $group->group_number,
        ], 200);
    }
}
