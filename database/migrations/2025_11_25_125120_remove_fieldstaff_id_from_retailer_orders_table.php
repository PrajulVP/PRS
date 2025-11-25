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
        // The fieldstaff_id column was never created in this table,
        // so no action is needed to drop it.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed to re-add a column that was never dropped.
    }
};
