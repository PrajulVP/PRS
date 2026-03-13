<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class PrescriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'retailer']);
    }

    /** @test */
    public function it_can_upload_prescription_and_get_ocr_results()
    {
        $ocrMock = Mockery::mock(OcrService::class);
        $ocrMock->shouldReceive('extractPrescription')
            ->once()
            ->andReturn([
                'medicines' => [
                    ['name' => 'Paracetamol', 'count' => 2]
                ]
            ]);
        
        $this->app->instance(OcrService::class, $ocrMock);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/prescriptions/upload', [
                'prescription' => UploadedFile::fake()->image('prescription.jpg')
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'medicines' => [
                    ['name' => 'Paracetamol', 'count' => 2]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_requires_a_prescription_file()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/prescriptions/upload', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['prescription']);
    }

    /** @test */
    public function it_returns_500_if_ocr_fails()
    {
        $ocrMock = Mockery::mock(OcrService::class);
        $ocrMock->shouldReceive('extractPrescription')
            ->once()
            ->andReturn(null);
        
        $this->app->instance(OcrService::class, $ocrMock);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/prescriptions/upload', [
                'prescription' => UploadedFile::fake()->image('prescription.jpg')
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to extract data from the prescription.'
        ]);
    }
}
