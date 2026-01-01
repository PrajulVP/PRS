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
        Schema::table('fieldstaffs', function (Blueprint $table) {
            $table->string('contact_no')->nullable()->after('sales_manager_id');
            $table->text('address')->nullable()->after('contact_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fieldstaffs', function (Blueprint $table) {
            $table->dropColumn(['contact_no', 'address']);
        });
    }
};
