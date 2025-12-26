<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::limit(4)->get();
        $products = Product::limit(6)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return; // Need customers and products first
        }

        // Transaction 1: iPhone purchase
        $transaction1 = Transaction::create([
            'invoice_number' => 'INV-20241208-ABC123',
            'customer_id' => $customers[0]->id,
            'subtotal' => 79990.00,
            'tax' => 9598.80,
            'total' => 89588.80,
            'payment_method' => 'card',
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 1,
                    'unit_price' => 79990.00,
                    'discount' => 0,
                    'tax' => 9598.80,
                    'line_total' => 89588.80,
                ],
            ],
        ]);

        // Attach products and log stock movement
        $this->attachProductsAndLog($transaction1, [
            [
                'product_id' => $products[0]->id,
                'quantity' => 1,
                'unit_price' => 79990.00,
                'line_total' => 89588.80,
            ],
        ]);

        // Transaction 2: Multiple accessories
        $transaction2 = Transaction::create([
            'invoice_number' => 'INV-20241208-DEF456',
            'customer_id' => null, // Walk-in
            'subtotal' => 4097.00,
            'tax' => 491.64,
            'total' => 4588.64,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $products[3]->id,
                    'quantity' => 2,
                    'unit_price' => 799.00,
                    'discount' => 0,
                    'tax' => 191.76,
                    'line_total' => 1789.76,
                ],
                [
                    'product_id' => $products[4]->id,
                    'quantity' => 1,
                    'unit_price' => 1999.00,
                    'discount' => 0,
                    'tax' => 239.88,
                    'line_total' => 2238.88,
                ],
                [
                    'product_id' => $products[5]->id,
                    'quantity' => 1,
                    'unit_price' => 1299.00,
                    'discount' => 200.00,
                    'tax' => 60.00,
                    'line_total' => 560.00,
                ],
            ],
        ]);

        $this->attachProductsAndLog($transaction2, [
            ['product_id' => $products[3]->id, 'quantity' => 2, 'unit_price' => 799.00, 'line_total' => 1789.76],
            ['product_id' => $products[4]->id, 'quantity' => 1, 'unit_price' => 1999.00, 'line_total' => 2238.88],
            ['product_id' => $products[5]->id, 'quantity' => 1, 'unit_price' => 1299.00, 'discount' => 200.00, 'line_total' => 560.00],
        ]);

        // Transaction 3: Laptop purchase
        $transaction3 = Transaction::create([
            'invoice_number' => 'INV-20241207-GHI789',
            'customer_id' => $customers[2]->id,
            'subtotal' => 119999.00,
            'tax' => 14399.88,
            'total' => 134398.88,
            'payment_method' => 'gcash',
            'items' => [
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 1,
                    'unit_price' => 119999.00,
                    'discount' => 0,
                    'tax' => 14399.88,
                    'line_total' => 134398.88,
                ],
            ],
        ]);

        $this->attachProductsAndLog($transaction3, [
            ['product_id' => $products[1]->id, 'quantity' => 1, 'unit_price' => 119999.00, 'line_total' => 134398.88],
        ]);
    }

    private function attachProductsAndLog(Transaction $transaction, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            // Attach to pivot
            $transaction->products()->attach($item['product_id'], [
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax' => $item['tax'] ?? 0,
                'line_total' => $item['line_total'],
            ]);

            // Update stock and log movement
            $oldStock = $product->stock_quantity;
            $product->decrement('stock_quantity', $item['quantity']);
            $newStock = $product->fresh()->stock_quantity;

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => -$item['quantity'],
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'reference_type' => Transaction::class,
                'reference_id' => $transaction->id,
                'notes' => 'Sale: ' . $transaction->invoice_number,
                'user_id' => 1, // Assuming admin user
            ]);
        }
    }
}
