<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists to prevent duplicate error
        if (! User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        // custom shortcut
        if (! User::where('email', 'x@x.com')->exists()) {
            User::create([
                'name' => 'Jane Admin',
                'email' => 'x@x.com',
                'password' => Hash::make('qwerasdf'),
                'role' => 'admin',
            ]);
        }

        if (! User::where('email', 'z@z.com')->exists()) {
            User::create([
                'name' => 'John Employee',
                'email' => 'z@z.com',
                'password' => Hash::make('qwerasdf'),
                'role' => 'employee',
            ]);
        }
    }
}
