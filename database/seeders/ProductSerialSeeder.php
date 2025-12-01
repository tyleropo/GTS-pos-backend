<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSerialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get serialized products (is_serialized = true)
        $serializedProducts = DB::table('products')
            ->where('is_serialized', true)
            ->get();

        $serials = [];
        $serialCounter = 1;

        foreach ($serializedProducts as $product) {
            // Generate multiple serial numbers per product based on stock
            $serialsToGenerate = min($product->stock_quantity, 20); // Cap at 20 serials per product
            
            for ($i = 1; $i <= $serialsToGenerate; $i++) {
                // Generate realistic serial number
                $serialNumber = strtoupper(substr($product->brand, 0, 3)) . 
                               date('Y') . 
                               str_pad($product->id, 3, '0', STR_PAD_LEFT) . 
                               str_pad($serialCounter, 5, '0', STR_PAD_LEFT);
                
                // Determine status distribution
                $statusDistribution = rand(1, 100);
                if ($statusDistribution <= 70) {
                    $status = 'available';
                    $soldAt = null;
                    $warrantyExpiresAt = null;
                } elseif ($statusDistribution <= 95) {
                    $status = 'sold';
                    $soldAt = now()->subDays(rand(1, 180));
                    $warrantyExpiresAt = $soldAt->copy()->addMonths($product->warranty_period ?? 12);
                } elseif ($statusDistribution <= 98) {
                    $status = 'returned';
                    $soldAt = now()->subDays(rand(1, 60));
                    $warrantyExpiresAt = null;
                } else {
                    $status = 'defective';
                    $soldAt = null;
                    $warrantyExpiresAt = null;
                }
                
                $serials[] = [
                    'product_id' => $product->id,
                    'serial_number' => $serialNumber,
                    'status' => $status,
                    'sold_at' => $soldAt,
                    'warranty_expires_at' => $warrantyExpiresAt,
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ];
                
                $serialCounter++;
            }
        }

        foreach ($serials as $serial) {
            DB::table('product_serials')->insert($serial);
        }
    }
}
