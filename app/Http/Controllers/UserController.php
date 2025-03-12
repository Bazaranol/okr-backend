<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;

class UserController extends Controller
{
    public function uploadCsv(Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0); // первая строка - заголовки

        foreach ($csv as $record) {
            $user = User::create([
                'name' => $record['name'],
                'email' => $record['email'],
                'password' => Hash::make($record['password']),
            ]);
        }

        return response()->json(['message' => 'Users uploaded successfully'], 200);
    }

    public function addRole(Request $request) {
        $request->validate([
            'user_id' => 'required',
            'role_name' => 'required',
        ]);

        $user = User::where('id', $request->user_id)->first();
        $role = Role::where('name', $request->role_name)->first();

        if ($role){
            $user->roles()->attach($role->id);
        }


        return response()->json(['message' => 'Role ' . $request->role_name . ' added to user with id:' . $request->user_id], 201);
    }
}
