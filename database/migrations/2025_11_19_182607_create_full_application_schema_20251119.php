<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Districts Table
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Areas Table
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Distributors Table
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('company_name');
            $table->string('drug_license_number')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('status')->default('active');
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // FieldStaffs Table
        Schema::create('fieldstaffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_distributor_id')->constrained('distributors')->onDelete('cascade');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Retailers Table
        Schema::create('retailers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->foreignId('fieldstaff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('shop_name');
            $table->string('proprietor_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active');
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->timestamps();
        });

        // Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_code')->unique();
            $table->decimal('mrp', 10, 2);
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        // Distributor Orders Table
        Schema::create('distributor_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // Added unique order code
            $table->foreignId('distributor_id')->constrained('distributors')->onDelete('cascade');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'dispatched', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('placed_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('prescription_photo')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->foreignId('fieldstaff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
            $table->timestamps();
        });

        // Retailer Orders Table
        Schema::create('retailer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // Added unique order code
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->foreignId('retailer_id')->constrained('retailers')->onDelete('cascade');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status', 50)->default('pending'); // Increased length to 50, default to pending
            $table->timestamp('placed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('fieldstaff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        // Invoices Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_order_id')->constrained('retailer_orders')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // Sales Targets Table
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fieldstaff_id')->constrained('fieldstaffs')->onDelete('cascade');
            $table->string('month');
            $table->year('year');
            $table->decimal('amount', 10, 2);
            $table->decimal('achieved_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        // Ratings Table
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_id')->constrained('retailers')->onDelete('cascade');
            $table->integer('rating'); // 1-5 scale or similar
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        // Remarks Table
        Schema::create('remarks', function (Blueprint $table) {
            $table->id();
            $table->morphs('remarkable'); // Polymorphic relation (e.g., for Retailer, Distributor, Order)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('remark');
            $table->timestamps();
        });

        // Permission Groups Table
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_code')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('system')->default(false);
            $table->timestamps();
        });

        // Permission Categories Table
        Schema::create('permission_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_group_id')->constrained('permission_groups')->onDelete('cascade'); // ADDED
            $table->string('name')->unique();
            $table->string('short_code')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('enable_view')->default(false); // ADDED
            $table->boolean('enable_add')->default(false);  // ADDED
            $table->boolean('enable_edit')->default(false); // ADDED
            $table->boolean('enable_delete')->default(false); // ADDED
            $table->timestamps();
        });

        // Spatie Permissions Tables
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 125); // Reduced length
            $table->string('guard_name', 125); // Reduced length
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 125); // Reduced length
            $table->string('guard_name', 125); // Reduced length
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'model_id', 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['role_id', 'model_id', 'model_type'],
                    'model_has_roles_role_model_type_primary');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Custom Roles Permissions (pivot table for Roles and PermissionCategories)
        Schema::create('roles_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_category_id')->constrained('permission_categories')->onDelete('cascade');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_add')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['role_id', 'permission_category_id'], 'role_permission_category_unique');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation
        Schema::dropIfExists('roles_permissions'); // ADDED
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        
        Schema::table('permission_categories', function (Blueprint $table) {
            $table->dropForeign(['permission_group_id']);
        });
        Schema::dropIfExists('permission_categories');
        Schema::dropIfExists('permission_groups');
        Schema::dropIfExists('remarks');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('retailer_orders');
        Schema::dropIfExists('distributor_orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('retailers');
        Schema::dropIfExists('fieldstaffs');
        Schema::dropIfExists('distributors');
        
        Schema::dropIfExists('areas');
        Schema::dropIfExists('districts');
    }
};
