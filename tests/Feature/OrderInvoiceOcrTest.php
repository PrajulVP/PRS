<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;
use App\Models\User;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderInvoiceOcrTest extends TestCase
{
    use RefreshDatabase;

    protected $distributorUser;
    protected $distributor;
    protected $order;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles for both guards
        Role::findOrCreate('distributor', 'api');
        Role::findOrCreate('retailer', 'api');
        Role::findOrCreate('distributor', 'web');
        Role::findOrCreate('retailer', 'web');

        // Create distributor user
        $this->distributorUser = User::factory()->distributor()->create();
        $this->distributorUser->assignRole('distributor');
        $this->distributor = Distributor::factory()->create(['user_id' => $this->distributorUser->id]);

        // Create retailer user  (no factory for Retailer — avoids schema mismatches)
        $retailerUser = User::factory()->retailer()->create();
        $retailerUser->assignRole('retailer');
        $retailer = Retailer::create([
            'user_id'        => $retailerUser->id,
            'distributor_id' => $this->distributor->id,
            'shop_name'      => 'Test Shop',
            'pincode'        => '123456',
            'credit_limit'   => 0,
        ]);

        // Create product and inventory
        $this->product = Product::factory()->create(['product_name' => 'Aspirin']);

        Inventory::create([
            'distributor_id'           => $this->distributor->id,
            'product_id'               => $this->product->id,
            'product_name'             => 'Aspirin',
            'distributor_product_code' => 'TEST-001',
            'batch_no'                 => 'B123',
            'stock'                    => 100,
            'expiry_date'              => now()->addYear(),
        ]);

        // Create a processing order
        $this->order = RetailerOrder::create([
            'distributor_id' => $this->distributor->id,
            'retailer_id'    => $retailer->id,
            'order_code'     => 'TEST-123',
            'status'         => 'processing',
            'total_amount'   => 100,
            'total_items'    => 1,
            'total_quantity' => 10,
            'placed_at'      => now(),
        ]);

        RetailerOrderItem::create([
            'retailer_order_id' => $this->order->id,
            'product_id'        => $this->product->id,
            'quantity'          => 10,
            'unit'              => 'Strip',
            'unit_price'        => 10,
            'total_amount'      => 100,
        ]);
    }

    /** @test */
    public function it_automatically_accepts_order_if_ocr_matches()
    {
        Storage::fake('public');

        $ocrMock = Mockery::mock(OcrService::class);
        $ocrMock->shouldReceive('processInvoice')->andReturn([
            'items' => [
                ['product_name' => 'Aspirin', 'quantity' => 10],
            ],
        ]);
        $this->app->instance(OcrService::class, $ocrMock);

        $response = $this->actingAs($this->distributorUser, 'api')
            ->postJson("/api/distributor/retailer-orders/{$this->order->id}/upload-invoice", [
                'invoice' => UploadedFile::fake()->create('invoice.pdf', 1000),
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->order->refresh();
        $this->assertEquals('accepted', $this->order->status);
        $this->assertNotNull($this->order->invoice_path);

        // Verify FEFO batch allocation happened
        $this->assertDatabaseHas('retailer_order_item_batches', ['batch_no' => 'B123']);
    }

    /** @test */
    public function it_returns_error_with_mismatch_data_if_ocr_quantity_is_low()
    {
        Storage::fake('public');

        $ocrMock = Mockery::mock(OcrService::class);
        $ocrMock->shouldReceive('processInvoice')->andReturn([
            'items' => [
                ['product_name' => 'Aspirin', 'quantity' => 5], // Less than ordered qty of 10
            ],
        ]);
        $this->app->instance(OcrService::class, $ocrMock);

        $response = $this->actingAs($this->distributorUser, 'api')
            ->postJson("/api/distributor/retailer-orders/{$this->order->id}/upload-invoice", [
                'invoice' => UploadedFile::fake()->create('invoice.pdf', 1000),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonStructure(['mismatches', 'ocr_data', 'expected_data', 'invoice_path']);

        // Order should remain processing
        $this->order->refresh();
        $this->assertEquals('processing', $this->order->status);
        // But invoice_path should be saved
        $this->assertNotNull($this->order->invoice_path);
    }

    /** @test */
    public function it_can_manually_accept_using_previously_uploaded_invoice()
    {
        // Simulate invoice already uploaded
        $this->order->update(['invoice_path' => 'retailer_invoices/existing_invoice.pdf']);

        $response = $this->actingAs($this->distributorUser, 'api')
            ->postJson("/api/distributor/retailer-orders/{$this->order->id}/accept", [
                'payment_status' => 'paid',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success']);

        $this->order->refresh();
        $this->assertEquals('accepted', $this->order->status);
        $this->assertEquals('paid', $this->order->payment_status);
    }
}
