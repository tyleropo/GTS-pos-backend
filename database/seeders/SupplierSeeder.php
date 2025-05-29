<?php

namespace Database\Seeders;

use App\Models\Supplier;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'TechVendor Inc.',
            'place' => 'New York',
            'negotiator' => 'Bob Smith',
        ]);

        Supplier::create([
            'name' => 'AudioTech Ltd.',
            'place' => 'Los Angeles',
            'negotiator' => 'Emily Brown',
        ]);

        Supplier::create([
            'name' => 'CompTech Systems',
            'place' => 'Chicago',
            'negotiator' => 'James Wilson',
        ]);
    }
}
