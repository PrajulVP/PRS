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
            $table->string('short_code')->nullable()->unique();
            $table->boolean('enable_view')->default(false);
            $table->boolean('enable_add')->default(false);
            $table->boolean('enable_edit')->default(false);
            $table->boolean('enable_delete')->default(false);
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
