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
            'first_name' => 'GTS Marketing',
            'last_name' => 'Admin',
            'email' => 'admin@gtsmarketing.com',
            'password' => 'GTSMarketing123.',
            'role' => 'manager',
        ]);

        User::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Bingo',
            'email' => 'janbingo@gtsmarketing.com',
            'password' => 'JanBingo123.',
            'role' => 'cashier',
        ]);
    }
}
