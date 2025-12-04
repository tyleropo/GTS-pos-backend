<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $users = User::all();

        if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Please ensure customers, products, and users exist first');
            return;
        }

        $cashier = $users->first();
        $transactionCount = 20;

        for ($i = 1; $i <= $transactionCount; $i++) {
            $customer = $customers->random();
            $itemCount = rand(1, 5);
            
            $subtotal = 0;
            $items = [];

            // Generate random items
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->selling_price;
                $lineDiscount = rand(0, 1) ? rand(0, 20) : 0;
                $lineTotal = ($unitPrice * $quantity) - $lineDiscount;
                
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_discount' => $lineDiscount,
                    'line_total' => $lineTotal,
                ];
            }

            $taxAmount = $subtotal * 0.08; // 8% tax
            $discountAmount = rand(0, 5) == 0 ? rand(10, 50) : 0;
            $grandTotal = $subtotal + $taxAmount - $discountAmount;

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => 'TXN-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'cashier_id' => $cashier->id,
                'transaction_date' => now()->subDays(rand(1, 30)),
                'transaction_time' => now()->subHours(rand(0, 23))->format('H:i:s'),
                'payment_method' => collect(['Cash', 'Credit Card', 'Debit Card'])->random(),
                'status' => rand(0, 10) == 0 ? 'Refunded' : 'Completed',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'notes' => rand(0, 5) == 0 ? 'Customer requested gift wrapping' : null,
            ]);

            // Create transaction items
            foreach ($items as $itemData) {
                $itemData['transaction_id'] = $transaction->id;
                TransactionItem::create($itemData);
            }
        }

        $this->command->info("Created {$transactionCount} transactions with items");
    }
}
