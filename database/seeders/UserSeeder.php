<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = [
            [
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin'),
                'roles' => ['admin', 'dean'],
            ],
            [
                'name' => 'dean',
                'email' => 'dean@dean.com',
                'password' => Hash::make('dean'),
                'roles' => ['dean'],
            ],
            [
                'name' => 'student',
                'email' => 'student@student.com',
                'password' => Hash::make('student'),
                'roles' => null
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['name'],
            ]);
            if ($userData['roles']){
                foreach ($userData['roles'] as $roleName){
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                }
            }
        }
    }
}
