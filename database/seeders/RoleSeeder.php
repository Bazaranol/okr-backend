<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'student']);
        Role::create(['name' => 'deanery']);
        Role::create(['name' => 'default']);
    }
}
