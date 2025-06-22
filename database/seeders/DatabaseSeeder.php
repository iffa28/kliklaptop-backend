<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'KlikTop Admin',
            'email' => 'adminkliktop@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('kliktopjaya28'),
            'remember_token' => Str::random(10),
            'role_id' => 1,
        ]);
    }
}
