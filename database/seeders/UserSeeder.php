<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@familyexpenses.com',
            'password' => 'admin123', // Will be hashed automatically
            'role' => 'admin',
            'isActive' => true,
        ]);

        // Create member users
        User::create([
            'name' => 'John Doe',
            'username' => 'john',
            'email' => 'john@familyexpenses.com',
            'password' => 'john123', // Will be hashed automatically
            'role' => 'member',
            'isActive' => true,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'username' => 'jane',
            'email' => 'jane@familyexpenses.com',
            'password' => 'jane123', // Will be hashed automatically
            'role' => 'member',
            'isActive' => true,
        ]);

        $this->command->info('Users seeded successfully!');
    }
}

