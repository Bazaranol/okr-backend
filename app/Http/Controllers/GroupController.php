<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request){
        $groups = Group::all();

        return response()->json($groups);
    }

    public function store(Request $request) {
        $request->validate([
            'group_number' => 'required|integer|unique:groups,group_number',
        ]);

        Group::create([
            'group_number'=> $request->input('group_number'),
        ]);

        return response()->json(['message' => 'Group created.'], 201);
    }

    public function getGroupUsers(Request $request)
    {
        $request->validate([
            'group_number' => 'required|exists:groups,group_number',
        ]);

        $group = Group::where('group_number', $request->group_number)->first();
        $users = $group->users;

        return response()->json(['data' => $users], 200);
    }


}
