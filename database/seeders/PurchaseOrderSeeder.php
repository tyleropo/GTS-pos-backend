<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::limit(3)->get();
        $products = Product::limit(5)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // PO 1: Submitted, pending delivery
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-20241205-XYZ123',
            'supplier_id' => $customers[0]->id,
            'status' => 'submitted',
            'payment_status' => 'pending',
            'expected_at' => now()->addDays(7),
            'subtotal' => 1375000.00,
            'tax' => 165000.00,
            'total' => 1540000.00,
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity_ordered' => 25,
                    'unit_cost' => 55000.00,
                ],
            ],
        ]);

        $po1->products()->attach($products[0]->id, [
            'quantity_ordered' => 25,
            'quantity_received' => 0,
            'unit_cost' => 55000.00,
            'tax' => 165000.00,
            'line_total' => 1540000.00,
        ]);

        // PO 2: Partially received
        $po2 = PurchaseOrder::create([
            'po_number' => 'PO-20241203-ABC456',
            'supplier_id' => $customers[1]->id,
            'status' => 'submitted',
            'payment_status' => 'pending',
            'expected_at' => now()->addDays(3),
            'subtotal' => 760000.00,
            'tax' => 91200.00,
            'total' => 851200.00,
            'items' => [
                [
                    'product_id' => $products[2]->id,
                    'quantity_ordered' => 20,
                    'unit_cost' => 38000.00,
                ],
            ],
        ]);

        $po2->products()->attach($products[2]->id, [
            'quantity_ordered' => 20,
            'quantity_received' => 10, // Partial delivery
            'unit_cost' => 38000.00,
            'tax' => 91200.00,
            'line_total' => 851200.00,
        ]);

        // Log stock movement for partial receipt
        $product = Product::find($products[2]->id);
        $oldStock = $product->stock_quantity - 10; // Reverse calculation
        
        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'previous_stock' => $oldStock,
            'new_stock' => $product->stock_quantity,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po2->id,
            'notes' => 'Partial receipt: ' . $po2->po_number,
            'user_id' => 1,
        ]);

        // PO 3: Fully received (completed)
        $po3 = PurchaseOrder::create([
            'po_number' => 'PO-20241130-DEF789',
            'supplier_id' => $customers[2]->id,
            'status' => 'received',
            'payment_status' => 'paid',
            'expected_at' => now()->subDays(2),
            'subtotal' => 96000.00,
            'tax' => 11520.00,
            'total' => 107520.00,
            'items' => [
                [
                    'product_id' => $products[4]->id,
                    'quantity_ordered' => 80,
                    'unit_cost' => 1200.00,
                ],
            ],
        ]);

        $po3->products()->attach($products[4]->id, [
            'quantity_ordered' => 80,
            'quantity_received' => 80, // Fully received
            'unit_cost' => 1200.00,
            'tax' => 11520.00,
            'line_total' => 107520.00,
        ]);

        // Log stock movement for full receipt
        $product2 = Product::find($products[4]->id);
        $oldStock2 = $product2->stock_quantity - 80;
        
        StockMovement::create([
            'product_id' => $product2->id,
            'type' => 'in',
            'quantity' => 80,
            'previous_stock' => $oldStock2,
            'new_stock' => $product2->stock_quantity,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po3->id,
            'notes' => 'Full receipt: ' . $po3->po_number,
            'user_id' => 1,
        ]);
    }
}
