<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::findOrCreate('admin', 'web');

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'company_name' => 'Demo Hosting Co',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles([$role]);
    }
}