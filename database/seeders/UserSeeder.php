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
                'fullName' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin'),
                'roles' => ['admin', 'dean'],
            ],
            [
                'fullName' => 'dean',
                'email' => 'dean@dean.com',
                'password' => Hash::make('dean'),
                'roles' => ['dean'],
            ],
            [
                'fullName' => 'teacher',
                'email' => 'teacher@teacher.com',
                'password' => Hash::make('teacher'),
                'roles' => ['teacher'],
            ],
            [
                'fullName' => 'student',
                'email' => 'student@student.com',
                'password' => Hash::make('student'),
                'roles' => null
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'fullName' => $userData['fullName'],
                'email' => $userData['email'],
                'password' => $userData['password'],
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
