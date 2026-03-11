<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\User;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class PrescriptionOcrTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $retailer;
    protected $distributor;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a distributor manually
        $distributorUser = User::factory()->create(['role' => 'distributor']);
        $this->distributor = Distributor::create([
            'user_id' => $distributorUser->id,
            'name' => 'Test Distributor',
            'latitude' => '12.9717',
            'longitude' => '77.5947',
            'contact_no' => '1234567890',
            'address' => 'Test Address',
            'pincode' => '123456'
        ]);

        // Create a retailer manually
        $retailerUser = User::factory()->create(['role' => 'retailer']);
        $this->retailer = Retailer::create([
            'user_id' => $retailerUser->id,
            'distributor_id' => $this->distributor->id,
            'shop_name' => 'Test Retailer Shop',
            'contact_no' => '0987654321',
            'address' => 'Retailer Address',
            'pincode' => '654321',
            'credit_limit' => 0,
            'latitude' => '12.9716',
            'longitude' => '77.5946'
        ]);

        $this->user = $retailerUser;

        $this->product = Product::create([
            'product_code' => 'P123',
            'product_name' => 'Paracetamol 500mg',
            'ptr' => 10.50,
            'mrp' => 15.00,
            'pack' => '10 Strips',
            'box_size' => 10,
            'carton_size' => 100
        ]);

        Inventory::create([
            'distributor_id' => $this->distributor->id,
            'product_id' => $this->product->id,
            'product_name' => 'Paracetamol 500mg',
            'distributor_product_code' => 'TEST-001',
            'stock' => 1000,
            'batch_no' => 'B1',
            'expiry_date' => now()->addYear()
        ]);
    }

    /** @test */
    public function it_can_extract_and_match_medicines_from_prescription()
    {
        $ocrMock = Mockery::mock(OcrService::class);
        $ocrMock->shouldReceive('extractPrescription')->once()->andReturn([
            'medicines' => [
                ['name' => 'Paracetamol', 'count' => 2]
            ]
        ]);
        $this->app->instance(OcrService::class, $ocrMock);

        $response = $this->actingAs($this->user)
            ->postJson(route('ocr.extract-prescription'), [
                'prescription' => UploadedFile::fake()->image('prescription.jpg'),
                'retailer_id' => $this->retailer->id
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'medicines');
        $response->assertJsonPath('medicines.0.product.product_name', 'Paracetamol 500mg');
        $response->assertJsonPath('medicines.0.quantity', 2);
    }
}
