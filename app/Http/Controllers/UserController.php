<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;

class UserController extends Controller
{
    public function index(Request $request) {
        $query = User::with(['roles', 'skips' => function($q) {
            $q->where('status', 'approved');
        }]);

        if ($request->has('role')) {
            $role = $request->role;
            $query->whereHas('roles', function($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($request->has('group')) {
            $group = $request->group;
            $query->whereHas('group', function($q) use ($group) {
                $q->where('group_number', $group);
            });
        }

        $users = $query->paginate(10);

        $users->getCollection()->transform(function($user) {
            $user->approved_skips_count = $user->skips->count();
            return $user;
        });

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]
        ], 200);
    }

    public function uploadCsv(Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0); // первая строка - заголовки

        foreach ($csv as $record) {
            $user = User::create([
                'fullName' => $record['fullName'],
                'email' => $record['email'],
                'password' => Hash::make($record['password']),
            ]);
        }

        return response()->json(['message' => 'Users uploaded successfully'], 200);
    }

    public function addRole(Request $request) {
        if (!auth()->user()->hasRole(['admin', 'dean'])) {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }

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

    public function changeRoles(Request $request) {
        if (!auth()->user()->hasRole(['admin', 'dean'])) {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users, id',
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name'
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        $user->roles()->detach();

        $roles = Role::whereIn('name', $request->roles)->pluck('id');
        $user->roles()->attach($roles);

        return response()->json(['message' => 'Roles for that user was updated'], 200);
    }

    public function addToGroup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_number' => 'required|exists:groups,group_number',
        ]);

        if (!auth()->user()->can('addToGroup', Group::class)) {
            return response()->json(['message' => 'You need to be admin or dean to add user to a group.'], 403);
        }

        $user = User::find($request->user_id);
        if (!$user->hasRole('student')) {
            return response()->json(['message' => 'User must have a student role to be added to a group.'], 403);
        }

        $group = Group::where('group_number', $request->group_number)->first();

        $user->group_id = $group->id;
        $user->save();

        return response()->json(['message' => 'User added to group.'], 201);
    }


    public function removeFromGroup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        if (!auth()->user()->can('removeFromGroup', Group::class)) {
            return response()->json(['message' => 'You must have an admin or dean role to remove users from a group.'], 403);
        }

        $user = User::find($request->user_id);

        $user->group_id = null;
        $user->save();

        return response()->json(['message' => 'User removed from group'], 200);
    }
}
