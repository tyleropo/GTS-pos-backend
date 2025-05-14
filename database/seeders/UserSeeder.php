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
            'name' => 'Tyler Christina D. Opo',
            'email' => 'fake1@admin.com',
            'password' => 'Hatdog123.',
        ]);

        User::factory()->create([
            'name' => 'Ma. Cristina M. Pasague',
            'email' => 'fake2@admin.com',
            'password' => 'Annyeong1.',
        ]);

        User::factory()->create([
            'name' => 'Jerico P. Paster',
            'email' => 'fake3@admin.com',
            'password' => 'JuswaTheGreat1.',
        ]);
    }
}
