<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = DB::table('customers')->where('is_active', true)->pluck('id')->toArray();
        $cashiers = DB::table('users')->whereIn('role', ['cashier', 'manager'])->pluck('id')->toArray();
        $products = DB::table('products')->where('is_active', true)->get()->keyBy('id');
        
        $paymentMethods = ['cash', 'credit_card', 'debit_card', 'gcash', 'paymaya'];
        $transactionTypes = ['sale' => 85, 'return' => 10, 'exchange' => 5]; // Percentage distribution
        
        $transactions = [];
        $transactionItems = [];
        $transactionCounter = 1;
        
        // Create 100 transactions over the past 90 days
        for ($i = 1; $i <= 100; $i++) {
            // Determine transaction type
            $rand = rand(1, 100);
            if ($rand <= 85) {
                $transactionType = 'sale';
            } elseif ($rand <= 95) {
                $transactionType = 'return';
            } else {
                $transactionType = 'exchange';
            }
            
            $transactionNumber = 'TXN-' . date('y') . date('m') . str_pad($transactionCounter, 5, '0', STR_PAD_LEFT);
            $customerId = rand(1, 10) <= 7 ? $customers[array_rand($customers)] : null; // 70% have customer
            $cashierId = $cashiers[array_rand($cashiers)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $transactionDate = now()->subDays(rand(0, 90));
            
            // Generate transaction items (1-5 items per transaction)
            $itemCount = rand(1, 5);
            $subtotal = 0;
            $taxAmount = 0;
            
            $itemsForTransaction = [];
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->selling_price;
                $discount = rand(0, 1) === 1 ? round($unitPrice * rand(5, 15) / 100, 2) : 0; // 50% chance of discount
                $lineTotal = ($unitPrice - $discount) * $quantity;
                $lineTax = round($lineTotal * ($product->tax_rate / 100), 2);
                
                $subtotal += $lineTotal;
                $taxAmount += $lineTax;
                
                $itemsForTransaction[] = [
                    'transaction_id' => $i,
                    'product_id' => $product->id,
                    'product_serial_id' => null, // Serial tracking not implemented in this seeder
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount * $quantity,
                    'line_total' => $lineTotal,
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ];
            }
            
            $discountAmount = rand(0, 10) === 1 ? round($subtotal * rand(5, 10) / 100, 2) : 0; // 10% chance
            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            
            // For cash payments, add some change
            $changeAmount = 0;
            if ($paymentMethod === 'cash') {
                $amountPaid = ceil($totalAmount / 100) * 100; // Round up to nearest 100
                $changeAmount = $amountPaid - $totalAmount;
            }
            
            $transactions[] = [
                'transaction_number' => $transactionNumber,
                'customer_id' => $customerId,
                'cashier_id' => $cashierId,
                'transaction_type' => $transactionType,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_reference' => in_array($paymentMethod, ['credit_card', 'debit_card', 'gcash', 'paymaya']) 
                    ? 'REF-' . strtoupper(substr($paymentMethod, 0, 3)) . '-' . rand(100000, 999999) 
                    : null,
                'change_amount' => $changeAmount,
                'status' => 'completed',
                'notes' => rand(1, 10) === 1 ? 'Customer requested gift wrapping' : null,
                'transaction_date' => $transactionDate,
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ];
            
            $transactionItems = array_merge($transactionItems, $itemsForTransaction);
            $transactionCounter++;
        }
        
        // Insert transactions
        foreach ($transactions as $transaction) {
            DB::table('transactions')->insert($transaction);
        }
        
        // Insert transaction items
        foreach ($transactionItems as $item) {
            DB::table('transaction_items')->insert($item);
        }
    }
}
