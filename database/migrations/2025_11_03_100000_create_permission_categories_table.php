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
        Schema::create('permission_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perm_group_id')->nullable()->constrained('permission_groups')->cascadeOnDelete();
            $table->string('name');
<<<<<<< HEAD
            $table->string('short_code')->nullable();
            $table->boolean('enable_view')->default(false);
            $table->boolean('enable_add')->default(false);
            $table->boolean('enable_edit')->default(false);
            $table->boolean('enable_delete')->default(false);
=======
            $table->string('short_code')->unique();
            $table->boolean('enable_view')->default(true);
            $table->boolean('enable_add')->default(true);
            $table->boolean('enable_edit')->default(true);
            $table->boolean('enable_delete')->default(true);
            $table->foreignId('perm_group_id')->nullable()->constrained('permission_groups')->cascadeOnDelete();
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_categories');
    }
};
