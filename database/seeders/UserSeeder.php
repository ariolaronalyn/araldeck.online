<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (Full access)
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
            ]
        );

        // 2. Encoder (Can upload and edit, but maybe not delete)
        User::updateOrCreate(
            ['email' => 'encoder@test.com'],
            [
                'name' => 'Data Encoder',
                'password' => Hash::make('encoder123'),
                'role' => 'encoder',
            ]
        );

        User::updateOrCreate(['email' => 'student@test.com'], [
            'name' => 'Test Student',
            'role' => 'student', // Changed from user
            'password' => bcrypt('password')
        ]);

        User::updateOrCreate(['email' => 'teacher@test.com'], [
            'name' => 'Professor Smith',
            'role' => 'teacher', // New Role
            'password' => bcrypt('password')
        ]);
    }
}