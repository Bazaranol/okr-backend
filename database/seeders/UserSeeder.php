<?php

namespace Database\Seeders;

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
        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin')
        ]);

        User::create([
            'name' => 'dean',
            'email' => 'dean@dean.com',
            'password' => Hash::make('dean')
        ]);

        User::create([
            'name' => 'student',
            'email' => 'student@student.com',
            'password' => Hash::make('student')
        ]);

        User::create([
            'name' => 'default',
            'email' => 'default@default.com',
            'password' => Hash::make('default')
        ]);
    }
}
