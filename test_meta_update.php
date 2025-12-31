<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Str;

// Create dummy data
$customer = Customer::first();
if (!$customer) {
    echo "Creating customer...\n";
    $customer = Customer::create([
        'name' => 'Test Customer',
        'type' => 'Regular'
    ]);
}

$product = Product::first();
if (!$product) {
    echo "Creating product...\n";
    $product = Product::create([
        'name' => 'Test Product',
        'sku' => 'TEST-' . Str::random(5),
        'cost_price' => 100,
        'selling_price' => 150,
        'stock_quantity' => 100
    ]);
}

// Create PO
echo "Creating PO...\n";
$po = PurchaseOrder::create([
    'id' => Str::uuid(),
    'po_number' => 'TEST-PO-' . time(),
    'supplier_id' => $customer->id,
    'status' => 'draft',
    'subtotal' => 100,
    'tax' => 12,
    'total' => 112,
    'items' => [],
    'meta' => ['foo' => 'bar']
]);

echo "Initial Meta: " . json_encode($po->meta) . "\n";

// Update PO
echo "Updating PO with new meta...\n";
try {
    $request = \Illuminate\Http\Request::create('/api/purchase-orders/' . $po->id, 'PUT', [
        'supplier_id' => $customer->id,
        'status' => 'draft',
        'subtotal' => 100,
        'tax' => 12,
        'total' => 112,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity_ordered' => 1,
                'unit_cost' => 100
            ]
        ],
        'meta' => ['taxRate' => 30, 'taxType' => 'exclusive']
    ]);

    $controller = new \App\Http\Controllers\Api\PurchaseOrderController();
    $response = $controller->update($request, $po);

    $updatedPo = $response->getData();
    echo "Updated Meta: " . json_encode($updatedPo->meta) . "\n";
    
    // Check if it's an object or array (json_decode default)
    $meta = $updatedPo->meta;
    if (isset($meta->taxRate) && $meta->taxRate === 30) {
        echo "SUCCESS: Meta updated correctly.\n";
    } else {
        echo "FAILURE: Meta not updated.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// Cleanup
$po->delete();
