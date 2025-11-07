<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Retailer;
use App\Models\District;
use App\Models\Area;
use App\Models\Distributor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /** @test */
    public function test_non_retailer_cannot_create_order()
    {
        // Create a user who is not a retailer
        $user = User::factory()->create();

        // Authenticate as the user
        $this->actingAs($user);

        // Post data to the order store route
        $response = $this->post(route('orders.store'), [
            'product_name' => 'Test Product',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        // Assert a 403 forbidden response
        $response->assertStatus(403);
    }

    /** @test */
    public function test_retailer_can_create_order()
    {
        // Create a district and area
        $district = District::create(['name' => 'Test District', 'code' => 'TD']);
        $area = Area::create(['district_id' => $district->id, 'name' => 'Test Area', 'pincode' => '12345']);

        // Create a distributor
        $distributorUser = User::factory()->create();
        $distributor = Distributor::create([
            'user_id' => $distributorUser->id,
            'district_id' => $district->id,
            'area_id' => $area->id,
        ]);

        // Create a user and a retailer associated with that user
        $user = User::factory()->create();
        $retailer = Retailer::create([
            'user_id' => $user->id,
            'distributor_id' => $distributor->id,
            'gst' => '12345ABCDE'
        ]);

        // Authenticate as the user
        $this->actingAs($user);

        // Post data to the order store route
        $response = $this->post(route('orders.store'), [
            'product_name' => 'Test Product',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        // Assert the order was created
        $this->assertDatabaseHas('orders', [
            'retailer_id' => $retailer->id,
            'product_name' => 'Test Product',
        ]);

        // Assert the user is redirected to the dashboard
        $response->assertRedirect(route('dashboard'));
    }
}
