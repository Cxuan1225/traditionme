<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SecurityRbacSeeder::class);

        $admin = User::query()->updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Jay',
            'password' => Hash::make('Admin123'),
        ]);

        $adminRole = Role::findByName('admin', 'web');
        $admin->syncRoles([$adminRole]);
    }
}
