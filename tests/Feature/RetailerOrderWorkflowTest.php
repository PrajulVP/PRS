<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\RetailerOrder;
use App\Models\Distributor;
use App\Models\Retailer;
use App\Models\FieldStaff;
use App\Models\Product; // Assuming products are involved in orders
use Spatie\Permission\Models\Role; // Assuming spatie/laravel-permission is used

class RetailerOrderWorkflowTest extends TestCase
{
    use RefreshDatabase; // Resets the database for each test

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist for testing
        Role::findOrCreate('superadmin');
        Role::findOrCreate('admin');
        Role::findOrCreate('manager');
        Role::findOrCreate('distributor');
        Role::findOrCreate('fieldstaff');
        Role::findOrCreate('retailer');
    }

    /** @test */
    public function a_distributor_can_accept_a_pending_retailer_order()
    {
        // 1. Setup: Create a distributor, retailer, and a pending order
        $distributorUser = User::factory()->create();
        $distributorUser->assignRole('distributor');
        $distributor = Distributor::factory()->create(['user_id' => $distributorUser->id]);

        $retailerUser = User::factory()->create();
        $retailerUser->assignRole('retailer');
        $retailer = Retailer::factory()->create([
            'user_id' => $retailerUser->id,
            'distributor_id' => $distributor->id,
        ]);

        $product = Product::factory()->create(); // Create a product

        $order = RetailerOrder::factory()->create([
            'retailer_id' => $retailer->id,
            'distributor_id' => $distributor->id,
            'product_name' => $product->product_name,
            'quantity' => 1,
            'unit_price' => $product->mrp,
            'total_amount' => $product->mrp,
            'status' => RetailerOrder::STATUS_PENDING,
        ]);

        // 2. Action: Distributor accepts the order
        $response = $this->actingAs($distributorUser)->postJson(
            route('distributor.retailer_orders.accept', $order->id)
        );

        // 3. Assert: Order status is updated
        $response->assertOk()
                 ->assertJson(['success' => 'Order accepted successfully!']);

        $this->assertDatabaseHas('retailer_orders', [
            'id' => $order->id,
            'status' => RetailerOrder::STATUS_ACCEPTED_BY_DISTRIBUTOR,
        ]);
    }

    /** @test */
    public function a_distributor_can_assign_a_field_staff_to_a_retailer_order()
    {
        // 1. Setup: Create distributor, field staff, retailer, and an accepted order
        $distributorUser = User::factory()->create();
        $distributorUser->assignRole('distributor');
        $distributor = Distributor::factory()->create(['user_id' => $distributorUser->id]);

        $fieldStaffUser = User::factory()->create();
        $fieldStaffUser->assignRole('fieldstaff');
        $fieldStaff = FieldStaff::factory()->create([
            'user_id' => $fieldStaffUser->id,
            'distributor_id' => $distributor->id, // Ensure field staff belongs to this distributor
        ]);

        $retailerUser = User::factory()->create();
        $retailerUser->assignRole('retailer');
        $retailer = Retailer::factory()->create([
            'user_id' => $retailerUser->id,
            'distributor_id' => $distributor->id,
        ]);

        $product = Product::factory()->create();

        $order = RetailerOrder::factory()->create([
            'retailer_id' => $retailer->id,
            'distributor_id' => $distributor->id,
            'product_name' => $product->product_name,
            'quantity' => 1,
            'unit_price' => $product->mrp,
            'total_amount' => $product->mrp,
            'status' => RetailerOrder::STATUS_ACCEPTED_BY_DISTRIBUTOR, // Order must be accepted first
        ]);

        // 2. Action: Distributor assigns field staff
        $response = $this->actingAs($distributorUser)->postJson(
            route('distributor.retailer_orders.assign-fieldstaff', $order->id),
            ['field_staff_id' => $fieldStaff->id]
        );

        // 3. Assert: Field staff is assigned and status is updated
        $response->assertOk()
                 ->assertJson(['success' => 'Field staff assigned successfully!']);

        $this->assertDatabaseHas('retailer_orders', [
            'id' => $order->id,
            'field_staff_id' => $fieldStaff->id,
            'status' => RetailerOrder::STATUS_ASSIGNED_TO_FIELDSTAFF,
        ]);
    }

    /** @test */
    public function a_retailer_can_confirm_delivery_of_assigned_order()
    {
        // 1. Setup: Create distributor, field staff, retailer, and an assigned order
        $distributorUser = User::factory()->create();
        $distributorUser->assignRole('distributor');
        $distributor = Distributor::factory()->create(['user_id' => $distributorUser->id]);

        $fieldStaffUser = User::factory()->create();
        $fieldStaffUser->assignRole('fieldstaff');
        $fieldStaff = FieldStaff::factory()->create([
            'user_id' => $fieldStaffUser->id,
            'distributor_id' => $distributor->id,
        ]);

        $retailerUser = User::factory()->create();
        $retailerUser->assignRole('retailer');
        $retailer = Retailer::factory()->create([
            'user_id' => $retailerUser->id,
            'distributor_id' => $distributor->id,
        ]);

        $product = Product::factory()->create();

        $order = RetailerOrder::factory()->create([
            'retailer_id' => $retailer->id,
            'distributor_id' => $distributor->id,
            'field_staff_id' => $fieldStaff->id,
            'product_name' => $product->product_name,
            'quantity' => 1,
            'unit_price' => $product->mrp,
            'total_amount' => $product->mrp,
            'status' => RetailerOrder::STATUS_ASSIGNED_TO_FIELDSTAFF,
        ]);

        // 2. Action: Retailer confirms delivery
        $response = $this->actingAs($retailerUser)->postJson(
            route('retailer.orders.confirmDelivery', $order->id)
        );

        // 3. Assert: Order status is delivered
        $response->assertOk()
                 ->assertJson(['success' => 'Order delivery confirmed successfully!']);

        $this->assertDatabaseHas('retailer_orders', [
            'id' => $order->id,
            'status' => RetailerOrder::STATUS_DELIVERED,
        ]);
    }


    /** @test */
    public function unauthorized_users_cannot_accept_retailer_orders()
    {
        // 1. Setup: Create a distributor, retailer, and a pending order
        $distributorUser = User::factory()->create();
        $distributorUser->assignRole('distributor');
        $distributor = Distributor::factory()->create(['user_id' => $distributorUser->id]);

        $retailerUser = User::factory()->create();
        $retailerUser->assignRole('retailer');
        $retailer = Retailer::factory()->create([
            'user_id' => $retailerUser->id,
            'distributor_id' => $distributor->id,
        ]);

        $product = Product::factory()->create();

        $order = RetailerOrder::factory()->create([
            'retailer_id' => $retailer->id,
            'distributor_id' => $distributor->id,
            'product_name' => $product->product_name,
            'quantity' => 1,
            'unit_price' => $product->mrp,
            'total_amount' => $product->mrp,
            'status' => RetailerOrder::STATUS_PENDING,
        ]);

        // Create another user with a different role (e.g., a manager)
        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('manager');

        // 2. Action: Unauthorized user tries to accept the order
        $response = $this->actingAs($unauthorizedUser)->postJson(
            route('distributor.retailer_orders.accept', $order->id)
        );

        // 3. Assert: Action is unauthorized
        $response->assertStatus(403) // Forbidden
                 ->assertJson(['error' => 'Unauthorized action.']);

        $this->assertDatabaseHas('retailer_orders', [
            'id' => $order->id,
            'status' => RetailerOrder::STATUS_PENDING, // Status should remain unchanged
        ]);
    }
}
