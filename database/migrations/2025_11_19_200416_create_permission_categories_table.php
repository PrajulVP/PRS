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
            $table->foreignId('perm_group_id')->constrained('permission_groups')->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('short_code')->unique();
            $table->boolean('is_active')->default(true);
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
        Schema::table('permission_categories', function (Blueprint $table) {
            $table->dropForeign(['perm_group_id']);
        });
        Schema::dropIfExists('permission_categories');
    }
};