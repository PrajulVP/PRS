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
        Schema::table('return_requests', function (Blueprint $table) {
            $table->renameColumn('tier1_approved_at', 'verified_at');
            $table->renameColumn('tier1_approved_by', 'verified_by');
            $table->renameColumn('tier2_approved_at', 'distributor_approved_at');
            $table->renameColumn('tier2_approved_by', 'distributor_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->renameColumn('verified_at', 'tier1_approved_at');
            $table->renameColumn('verified_by', 'tier1_approved_by');
            $table->renameColumn('distributor_approved_at', 'tier2_approved_at');
            $table->renameColumn('distributor_approved_by', 'tier2_approved_by');
        });
    }
};
