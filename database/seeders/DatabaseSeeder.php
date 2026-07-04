<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'tranquangtruong25@gmail.com'],
            [
                'name' => 'admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );


        $this->call([
            PartSeeder::class,
        ]);
    }
}
