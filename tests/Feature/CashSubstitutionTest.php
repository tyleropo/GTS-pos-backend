<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashSubstitutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_convert_order_line_to_cash_and_maintain_total()
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'roles' => ['admin']
        ]);
        
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '1234567890',
        ]);
        
        $productA = Product::create([
            'name' => 'Product A',
            'sku' => 'PROD-A',
            'price' => 100,
            'stock_quantity' => 10,
            'reorder_level' => 5,
            'reorder_quantity' => 10,
        ]);
        
        $productB = Product::create([
            'name' => 'Product B',
            'sku' => 'PROD-B',
            'price' => 50,
            'stock_quantity' => 10,
            'reorder_level' => 5,
            'reorder_quantity' => 10,
        ]);

        // Create Order
        // Line A: 100 * 1 = 100. Tax (let's say 10%) = 10. Total Line = 110.
        // Line B: 50 * 1 = 50. Tax = 5. Total Line = 55.
        // Grand Total = 165.
        
        $order = CustomerOrder::create([
            'co_number' => 'TEST-001',
            'customer_id' => $customer->id,
            'status' => 'draft',
            'subtotal' => 150.00,
            'tax' => 15.00,
            'total' => 165.00,
            'items' => [], // simplified for test creation, pivot is what matters
        ]);

        $order->products()->attach($productA->id, [
            'quantity_ordered' => 1,
            'quantity_fulfilled' => 0,
            'unit_cost' => 100.00,
            'tax' => 10.00,
            'line_total' => 100.00,
        ]);

        $order->products()->attach($productB->id, [
            'quantity_ordered' => 1,
            'quantity_fulfilled' => 0,
            'unit_cost' => 50.00,
            'tax' => 5.00,
            'line_total' => 50.00,
        ]);

        // Act: Convert Product A to Cash
        $response = $this->actingAs($user)
            ->postJson("/api/customer-orders/{$order->id}/convert-to-cash", [
                'product_id' => $productA->id
            ]);

        $response->assertStatus(200);

        // Access fresh order
        $order->refresh();
        $pivotA = $order->products()->where('product_id', $productA->id)->first()->pivot;

        // Assert Line A is voided
        $this->assertTrue((bool)$pivotA->is_voided);
        $this->assertEquals('Converted to Cash', $pivotA->void_reason);

        // Assert Adjustment Created
        $this->assertCount(1, $order->adjustments);
        $adjustment = $order->adjustments->first();
        $this->assertEquals('cash_payout', $adjustment->type);
        // Adjustment Amount = Line Total (100) + Tax (10) = 110
        $this->assertEquals(110.00, $adjustment->amount);
        $this->assertEquals($productA->id, $adjustment->related_product_id);

        // Assert Totals
        // Subtotal = Active Lines (50) + Adjustments (110) = 160.
        // Tax = Active Lines Only (5) = 5.
        // Total = 160 + 5 = 165.
        // Original Total was 165. So "Bill Full Amount" holds true.
        
        $this->assertEquals(160.00, $order->subtotal, 'Subtotal mismatch');
        $this->assertEquals(5.00, $order->tax, 'Tax mismatch');
        $this->assertEquals(165.00, $order->total, 'Total mismatch');
    }

    public function test_fulfillment_ignores_voided_lines()
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'roles' => ['admin']
        ]);
        
        $customer = Customer::create([
            'name' => 'Test Customer 2',
            'email' => 'customer2@example.com',
            'phone' => '1234567890',
        ]);
        
        $productA = Product::create([
            'name' => 'Product C',
            'sku' => 'PROD-C',
            'price' => 100,
            'stock_quantity' => 10,
            'reorder_level' => 5,
            'reorder_quantity' => 10,
        ]);

        $order = CustomerOrder::create([
            'co_number' => 'TEST-002',
            'customer_id' => $customer->id,
            'status' => 'draft',
            'subtotal' => 100.00,
            'tax' => 0.00,
            'total' => 100.00,
            'items' => [], 
        ]);

        $order->products()->attach($productA->id, [
            'quantity_ordered' => 1,
            'quantity_fulfilled' => 0,
            'unit_cost' => 100.00,
            'line_total' => 100.00,
            'is_voided' => true, // Simulate already voided
            'void_reason' => 'Test'
        ]);

        // Act: Try to fulfill
        $response = $this->actingAs($user)
            ->postJson("/api/customer-orders/{$order->id}/fulfill", [
                'items' => [
                    [
                        'product_id' => $productA->id,
                        'quantity_fulfilled' => 1
                    ]
                ]
            ]);

        $response->assertStatus(200);
        
        // Assert Pivot NOT updated
        $order->refresh();
        $pivot = $order->products()->first()->pivot;
        $this->assertEquals(0, $pivot->quantity_fulfilled);
        
        // Assert Stock NOT deducted
        $this->assertEquals(10, $productA->fresh()->stock_quantity);
    }
}
