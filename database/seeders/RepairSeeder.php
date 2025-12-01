<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RepairSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = DB::table('customers')->where('is_active', true)->pluck('id')->toArray();
        $technicians = DB::table('users')->where('role', 'technician')->pluck('id')->toArray();
        $products = DB::table('products')->where('is_active', true)->get()->keyBy('id');
        
        $deviceTypes = ['Smartphone', 'Laptop', 'Tablet', 'Smartwatch', 'Gaming Console'];
        $brands = ['Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Oppo', 'Vivo', 'Dell', 'Lenovo', 'HP', 'Asus'];
        $issues = [
            'Screen cracked/damaged',
            'Battery not charging',
            'Water damage',
            'Software issues/won\'t boot',
            'Charging port not working',
            'Speaker/microphone not working',
            'Camera malfunction',
            'Overheating issues',
            'Button/touchscreen not responsive',
            'Hardware component failure'
        ];
        
        $statuses = ['received', 'diagnosed', 'waiting_approval', 'in_progress', 'waiting_parts', 'completed', 'ready_pickup', 'delivered'];
        $priorities = ['low', 'normal', 'high', 'urgent'];
        
        $repairs = [];
        $repairParts = [];
        $repairCounter = 1;
        
        // Create 40 repair jobs over the past 60 days
        for ($i = 1; $i <= 40; $i++) {
            $repairNumber = 'REP-' . date('y') . date('m') . str_pad($repairCounter, 4, '0', STR_PAD_LEFT);
            $customerId = $customers[array_rand($customers)];
            $technicianId = rand(1, 10) <= 8 ? $technicians[array_rand($technicians)] : null; // 80% assigned
            $deviceType = $deviceTypes[array_rand($deviceTypes)];
            $brand = $brands[array_rand($brands)];
            $issue = $issues[array_rand($issues)];
            $priority = $priorities[array_rand($priorities)];
            $receivedDate = now()->subDays(rand(0, 60));
            
            // Determine status based on age
            $daysOld = now()->diffInDays($receivedDate);
            if ($daysOld < 2) {
                $status = ['received', 'diagnosed'][array_rand(['received', 'diagnosed'])];
            } elseif ($daysOld < 5) {
                $status = ['diagnosed', 'waiting_approval', 'in_progress'][array_rand(['diagnosed', 'waiting_approval', 'in_progress'])];
            } elseif ($daysOld < 10) {
                $status = ['in_progress', 'waiting_parts', 'completed'][array_rand(['in_progress', 'waiting_parts', 'completed'])];
            } else {
                $status = ['completed', 'ready_pickup', 'delivered'][array_rand(['completed', 'ready_pickup', 'delivered'])];
            }
            
            // Cost calculations
            $laborCost = rand(500, 3000);
            $partsCost = 0;
            
            // Add repair parts for some repairs
            if (rand(1, 10) <= 7) { // 70% of repairs need parts
                $partsCount = rand(1, 3);
                for ($j = 0; $j < $partsCount; $j++) {
                    $product = $products->random();
                    $quantity = 1;
                    $unitCost = $product->cost_price;
                    $partsCost += $unitCost * $quantity;
                    
                    $repairParts[] = [
                        'repair_id' => $i,
                        'product_id' => $product->id,
                        'quantity_used' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => $unitCost * $quantity,
                        'created_at' => $receivedDate,
                        'updated_at' => $receivedDate,
                    ];
                }
            }
            
            $estimatedCost = $laborCost + $partsCost;
            $actualCost = in_array($status, ['completed', 'ready_pickup', 'delivered']) 
                ? $estimatedCost + rand(-500, 500) 
                : null;
            
            $estimatedCompletionDate = $receivedDate->copy()->addDays(rand(3, 14));
            $actualCompletionDate = in_array($status, ['completed', 'ready_pickup', 'delivered'])
                ? $receivedDate->copy()->addDays(rand(2, 15))
                : null;
            
            $diagnosis = in_array($status, ['received']) ? null : 
                ($issue === 'Screen cracked/damaged' ? 'Display needs replacement. No other damage found.' :
                ($issue === 'Battery not charging' ? 'Battery swollen and needs replacement. Charging port OK.' :
                ($issue === 'Water damage' ? 'Liquid indicators triggered. Needs thorough cleaning and component check.' :
                'Diagnosed: ' . $issue)));
            
            $repairs[] = [
                'repair_number' => $repairNumber,
                'customer_id' => $customerId,
                'technician_id' => $technicianId,
                'device_type' => $deviceType,
                'device_brand' => $brand,
                'device_model' => $brand . ' ' . ['Pro', 'Max', 'Ultra', 'Plus', 'Lite'][array_rand(['Pro', 'Max', 'Ultra', 'Plus', 'Lite'])],
                'serial_number' => strtoupper(substr($brand, 0, 3)) . rand(100000, 999999),
                'issue_description' => $issue . '. Customer reports device has been problematic for ' . rand(1, 30) . ' days.',
                'diagnosis' => $diagnosis,
                'repair_notes' => in_array($status, ['in_progress', 'completed', 'ready_pickup', 'delivered']) 
                    ? 'Replaced faulty components. Tested all functions. Device working properly.' 
                    : null,
                'estimated_cost' => $estimatedCost,
                'actual_cost' => $actualCost,
                'labor_cost' => $laborCost,
                'parts_cost' => $partsCost,
                'status' => $status,
                'priority' => $priority,
                'received_date' => $receivedDate,
                'estimated_completion_date' => $estimatedCompletionDate,
                'actual_completion_date' => $actualCompletionDate,
                'warranty_period' => 30,
                'created_at' => $receivedDate,
                'updated_at' => $actualCompletionDate ?? $receivedDate,
            ];
            
            $repairCounter++;
        }
        
        // Insert repairs
        foreach ($repairs as $repair) {
            DB::table('repairs')->insert($repair);
        }
        
        // Insert repair parts
        foreach ($repairParts as $part) {
            DB::table('repair_parts')->insert($part);
        }
    }
}
