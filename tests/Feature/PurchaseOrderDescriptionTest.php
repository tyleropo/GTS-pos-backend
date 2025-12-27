<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Customer; // User or Customer? Supplier_id refers to customers table based on previous context
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderDescriptionTest extends TestCase
{
    use RefreshDatabase; // Be careful with RefreshDatabase on a persistent dev env. Maybe just clean up created data.

    public function test_purchase_order_can_save_and_update_description()
    {
        // 1. Setup Data
        $user = User::first(); // Assuming a user exists
        if (!$user) {
            $user = User::factory()->create();
        }
        
        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::create([
                'name' => 'Test Customer',
                'email' => 'test@example.com', 
                'phone' => '1234567890',
                'address' => '123 Test St',
                // Add validation fields if needed
            ]);
        }

        $product = Product::first();
        if (!$product) {
            $product = Product::create([
                'name' => 'Test Product',
                'sku' => 'TEST-' . rand(1000, 9999),
                'cost_price' => 100,
                'selling_price' => 120,
                'stock_quantity' => 10,
            ]);
        }

        // 2. Create PO with description
        $payload = [
            'supplier_id' => $customer->id,
            'status' => 'draft',
            'subtotal' => 100,
            'tax' => 12,
            'total' => 112,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity_ordered' => 2,
                    'unit_cost' => 50,
                    'line_total' => 100,
                    'description' => 'Test Item Description',
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson('/api/purchase-orders', $payload);
        if ($response->status() !== 201) {
            dump($response->json());
        }
        
        $response->assertStatus(201);
        $poId = $response->json('id');
        
        $this->assertDatabaseHas('product_purchase_order', [
            'purchase_order_id' => $poId,
            'product_id' => $product->id,
            'description' => 'Test Item Description'
        ]);
        
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $poId,
            'notes' => null
        ]);

        // 3. Update PO with new description (and syncing items)
        $updatePayload = [
            'supplier_id' => $customer->id, // Required for update validation now
            'status' => 'draft', // Required
            'expected_at' => now()->addDays(7)->format('Y-m-d'),
            'subtotal' => 150, // Required
            'tax' => 0, // Required
            'total' => 150, // Required
            'notes' => 'This is a test note',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity_ordered' => 3, // Changed quantity
                    'unit_cost' => 50,
                    'line_total' => 150,
                    'description' => 'Updated Description', // Changed description
                ]
            ],
            // 'subtotal' => 150, // Optional updates
        ];

        $responseUpdate = $this->actingAs($user)->putJson("/api/purchase-orders/{$poId}", $updatePayload);
        
        $responseUpdate->assertStatus(200);

        $this->assertDatabaseHas('product_purchase_order', [
            'purchase_order_id' => $poId,
            'product_id' => $product->id,
            'quantity_ordered' => 3,
            'description' => 'Updated Description'
        ]);
        
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $poId,
            'notes' => 'This is a test note'
        ]);

        // Cleanup
        PurchaseOrder::find($poId)->delete();
    }
    public function test_update_preserves_quantity_received()
    {
        $user = User::first() ?? User::factory()->create();
        $customer = Customer::first() ?? Customer::create(['name' => 'Cust', 'email' => 'c@e.com', 'phone' => '1', 'address' => 'A']);
        $product = Product::first() ?? Product::create(['name' => 'Prod', 'sku' => 'SKU', 'cost_price' => 10, 'selling_price' => 20, 'stock_quantity' => 10]);

        // Create PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . uniqid(),
            'supplier_id' => $customer->id,
            'status' => 'received',
            'subtotal' => 10, 'tax' => 0, 'total' => 10, 'expected_at' => now(), 'notes' => 'Old Note'
        ]);
        
        // Attach product with quantity_received = 5
        $po->products()->attach($product->id, [
            'quantity_ordered' => 10,
            'quantity_received' => 5, // SIMULATE PARTIAL/FULL RECEIPT
            'unit_cost' => 1,
            'line_total' => 10,
            'description' => 'Original',
            'tax' => 0
        ]);

        // Verify initial state
        $this->assertDatabaseHas('product_purchase_order', [
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity_received' => 5
        ]);

        // Update PO (change notes only, but send items array as required)
        $updatePayload = [
            'supplier_id' => $customer->id,
            'status' => 'received',
            'expected_at' => now()->format('Y-m-d'),
            'subtotal' => 10, 'tax' => 0, 'total' => 10,
            'notes' => 'New Note',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity_ordered' => 10,
                    'unit_cost' => 1,
                    // Frontend usually sends what it has.
                    // If frontend didn't send quantity_received, it used to default to 0 in controller.
                    // We want to ensure it STAYS 5.
                    'description' => 'Original', 
                ]
            ]
        ];

        $this->actingAs($user)->putJson("/api/purchase-orders/{$po->id}", $updatePayload)->assertStatus(200);

        // Assert quantity_received is STILL 5
        $this->assertDatabaseHas('product_purchase_order', [
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity_received' => 5,
            'quantity_ordered' => 10
        ]);
        
        // Assert notes updated
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'notes' => 'New Note'
        ]);
    }
}
