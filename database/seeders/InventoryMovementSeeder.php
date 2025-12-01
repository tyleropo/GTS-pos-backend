<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = DB::table('products')->where('is_active', true)->pluck('id')->toArray();
        $users = DB::table('users')->where('is_active', true)->pluck('id')->toArray();
        $transactions = DB::table('transactions')->pluck('id')->toArray();
        $purchaseOrders = DB::table('purchase_orders')->pluck('id')->toArray();
        
        $movementTypes = ['purchase', 'sale', 'return', 'adjustment', 'damage', 'transfer'];
        $movements = [];
        
        // Create inventory movements based on existing transactions
        // Sales movements
        foreach ($transactions as $transactionId) {
            $transaction = DB::table('transactions')->find($transactionId);
            $items = DB::table('transaction_items')->where('transaction_id', $transactionId)->get();
            
            foreach ($items as $item) {
                $movements[] = [
                    'product_id' => $item->product_id,
                    'movement_type' => $transaction->transaction_type === 'return' ? 'return' : 'sale',
                    'quantity' => $transaction->transaction_type === 'return' ? $item->quantity : -$item->quantity,
                    'reference_type' => 'transaction',
                    'reference_id' => $transactionId,
                    'notes' => 'Transaction ' . $transaction->transaction_number,
                    'created_at' => $transaction->transaction_date,
                    'updated_at' => $transaction->transaction_date,
                ];
            }
        }
        
        // Purchase movements from 10 random POs
        $randomPOs = array_rand(array_flip($purchaseOrders), min(10, count($purchaseOrders)));
        if (!is_array($randomPOs)) {
            $randomPOs = [$randomPOs];
        }
        
        foreach ($randomPOs as $poId) {
            $po = DB::table('purchase_orders')->find($poId);
            $items = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->get();
            
            foreach ($items as $item) {
                if ($item->quantity_received > 0) {
                    $movements[] = [
                        'product_id' => $item->product_id,
                        'movement_type' => 'purchase',
                        'quantity' => $item->quantity_received,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $poId,
                        'notes' => 'PO ' . $po->po_number . ' - Received',
                        'created_at' => $po->actual_delivery_date ?? $po->order_date,
                        'updated_at' => $po->actual_delivery_date ?? $po->order_date,
                    ];
                }
            }
        }
        
        // Manual adjustments and damage movements
        for ($i = 0; $i < 15; $i++) {
            $productId = $products[array_rand($products)];
            $userId = $users[array_rand($users)];
            $movementTimestamp = now()->subDays(rand(1, 60));
            
            if (rand(1, 10) <= 7) {
                // Adjustment
                $quantity = rand(1, 10) * (rand(0, 1) === 1 ? 1 : -1);
                $movements[] = [
                    'product_id' => $productId,
                    'movement_type' => 'adjustment',
                    'quantity' => $quantity,
                    'reference_type' => null,
                    'reference_id' => null,
                    'notes' => $quantity > 0 ? 'Stock count correction - added' : 'Stock count correction - removed',
                    'created_at' => $movementTimestamp,
                    'updated_at' => $movementTimestamp,
                ];
            } else {
                // Damage
                $movements[] = [
                    'product_id' => $productId,
                    'movement_type' => 'damage',
                    'quantity' => -rand(1, 5),
                    'reference_type' => null,
                    'reference_id' => null,
                    'notes' => ['Damaged during handling', 'Defective unit found', 'Water damage in storage'][array_rand(['Damaged during handling', 'Defective unit found', 'Water damage in storage'])],
                    'created_at' => $movementTimestamp,
                    'updated_at' => $movementTimestamp,
                ];
            }
        }
        
        // Insert all movements
        foreach ($movements as $movement) {
            DB::table('inventory_movements')->insert($movement);
        }
    }
}
