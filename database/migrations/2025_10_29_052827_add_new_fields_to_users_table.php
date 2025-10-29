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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->nullable();
            }
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'gst')) {
                $table->string('gst')->nullable();
            }
            if (!Schema::hasColumn('users', 'regulations')) {
                $table->string('regulations')->nullable();
            }
            if (!Schema::hasColumn('users', 'contact_no')) {
                $table->string('contact_no')->nullable();
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('users', 'pincode')) {
                $table->string('pincode')->nullable();
            }
            if (!Schema::hasColumn('users', 'district_id')) {
                $table->foreignId('district_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('users', 'area_id')) {
                $table->foreignId('area_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('users', 'route')) {
                $table->string('route')->nullable();
            }
            if (!Schema::hasColumn('users', 'distributor_id')) {
                $table->foreignId('distributor_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('users', 'assigned_distributor_id')) {
                $table->foreignId('assigned_distributor_id')->nullable()->constrained('distributors')->onDelete('cascade');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('company_name');
            $table->dropColumn('gst');
            $table->dropColumn('regulations');
            $table->dropColumn('contact_no');
            $table->dropColumn('address');
            $table->dropColumn('pincode');
            $table->dropForeign(['district_id']);
            $table->dropColumn('district_id');
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->dropColumn('route');
            $table->dropForeign(['distributor_id']);
            $table->dropColumn('distributor_id');
            $table->dropForeign(['assigned_distributor_id']);
            $table->dropColumn('assigned_distributor_id');
            $table->dropColumn('status');
        });
    }
};
