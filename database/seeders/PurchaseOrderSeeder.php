<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = DB::table('suppliers')->where('is_active', true)->pluck('id')->toArray();
        $managers = DB::table('users')->whereIn('role', ['admin', 'manager'])->pluck('id')->toArray();
        $products = DB::table('products')->where('is_active', true)->get()->keyBy('id');
        
        $statuses = ['pending', 'approved', 'ordered', 'partially_received', 'received', 'cancelled'];
        $purchaseOrders = [];
        $purchaseOrderItems = [];
        $poCounter = 1;
        
        // Create 30 purchase orders over the past 120 days
        for ($i = 1; $i <= 30; $i++) {
            $poNumber = 'PO-' . date('y') . date('m') . str_pad($poCounter, 4, '0', STR_PAD_LEFT);
            $supplierId = $suppliers[array_rand($suppliers)];
            $createdBy = $managers[array_rand($managers)];
            $orderDate = now()->subDays(rand(0, 120));
            
            // Determine status based on order date
            $daysOld = now()->diffInDays($orderDate);
            if ($daysOld < 7) {
                $status = ['pending', 'approved'][array_rand(['pending', 'approved'])];
            } elseif ($daysOld < 15) {
                $status = ['approved', 'ordered'][array_rand(['approved', 'ordered'])];
            } elseif ($daysOld < 30) {
                $status = ['ordered', 'partially_received', 'received'][array_rand(['ordered', 'partially_received', 'received'])];
            } else {
                $status = rand(1, 10) === 1 ? 'cancelled' : 'received';
            }
            
            $expectedDeliveryDate = $orderDate->copy()->addDays(rand(14, 45));
            $actualDeliveryDate = in_array($status, ['received', 'partially_received']) 
                ? $orderDate->copy()->addDays(rand(10, 50))
                : null;
            
            // Generate PO items (3-10 items per PO)
            $itemCount = rand(3, 10);
            $subtotal = 0;
            
            $itemsForPO = [];
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(5, 50);
                $unitCost = $product->cost_price;
                $lineTotal = $unitCost * $quantity;
                
                $quantityReceived = 0;
                if ($status === 'received') {
                    $quantityReceived = $quantity;
                } elseif ($status === 'partially_received') {
                    $quantityReceived = rand(floor($quantity * 0.3), floor($quantity * 0.8));
                }
                
                $subtotal += $lineTotal;
                
                $itemsForPO[] = [
                    'purchase_order_id' => $i,
                    'product_id' => $product->id,
                    'quantity_ordered' => $quantity,
                    'quantity_received' => $quantityReceived,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                    'created_at' => $orderDate,
                    'updated_at' => $actualDeliveryDate ?? $orderDate,
                ];
            }
            
            $taxAmount = round($subtotal * 0.12, 2);
            $totalAmount = $subtotal + $taxAmount;
            
            $purchaseOrders[] = [
                'po_number' => $poNumber,
                'supplier_id' => $supplierId,
                'created_by' => $createdBy,
                'order_date' => $orderDate,
                'expected_delivery_date' => $expectedDeliveryDate,
                'actual_delivery_date' => $actualDeliveryDate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $status,
                'notes' => rand(1, 10) === 1 ? 'Rush order - expedited shipping' : null,
                'created_at' => $orderDate,
                'updated_at' => $actualDeliveryDate ?? $orderDate,
            ];
            
            $purchaseOrderItems = array_merge($purchaseOrderItems, $itemsForPO);
            $poCounter++;
        }
        
        // Insert purchase orders
        foreach ($purchaseOrders as $po) {
            DB::table('purchase_orders')->insert($po);
        }
        
        // Insert purchase order items
        foreach ($purchaseOrderItems as $item) {
            DB::table('purchase_order_items')->insert($item);
        }
    }
}
