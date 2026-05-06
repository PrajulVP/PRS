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
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('return_code')->unique();
            $table->string('order_type'); // 'retailer' or 'distributor'
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id'); // Requester (Retailer user or Distributor user)
            
            // Item details
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->string('side')->nullable();
            $table->string('size')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('Nos');
            
            // Return info
            $table->text('reason');
            $table->string('image_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved_tier1, approved_tier2, completed, rejected
            
            // Financials (calculated at approval)
            $table->decimal('refund_amount', 15, 2)->default(0);
            
            // Approval tracking
            $table->timestamp('tier1_approved_at')->nullable();
            $table->unsignedBigInteger('tier1_approved_by')->nullable();
            $table->timestamp('tier2_approved_at')->nullable();
            $table->unsignedBigInteger('tier2_approved_by')->nullable();
            $table->timestamp('admin_approved_at')->nullable();
            $table->unsignedBigInteger('admin_approved_by')->nullable();
            
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();

            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_code')->unique();
            $table->unsignedBigInteger('user_id'); // Beneficiary
            $table->unsignedBigInteger('return_request_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2); // Remaining amount to be used
            $table->string('status')->default('active'); // active, partially_used, used, expired
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add credit_balance to users or specific profiles?
        // Let's add it to retailers and distributors profiles for easier tracking.
        Schema::table('retailers', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 2)->default(0)->after('credit_limit');
        });

        Schema::table('distributors', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 2)->default(0)->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
        Schema::table('distributors', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('return_requests');
    }
};
