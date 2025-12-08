<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'first_name' => 'Tyler Christina',
            'last_name' => 'Opo',
            'email' => 'fake1@admin.com',
            'password' => 'Hatdog123.',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'first_name' => 'Ma. Cristina',
            'last_name' => 'Pasague',
            'email' => 'fake2@admin.com',
            'password' => 'Annyeong1.',
            'role' => 'manager',
        ]);

        User::factory()->create([
            'first_name' => 'Jerico',
            'last_name' => 'Paster',
            'email' => 'fake3@admin.com',
            'password' => 'JuswaTheGreat1.',
            'role' => 'cashier',
        ]);
    }
}
