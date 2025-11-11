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
        Schema::table('roles_permissions', function (Blueprint $table) {
            // Drop existing foreign key constraint if it exists
            $table->dropForeign(['permission_id']);
            // Drop the unique index
            $table->dropUnique('roles_permissions_role_id_permission_id_unique');
            // Drop the permission_id column
            $table->dropColumn('permission_id');

            // Add new permission_category_id column
            $table->foreignId('permission_category_id')->nullable()->constrained('permission_categories')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles_permissions', function (Blueprint $table) {
            // Re-add permission_id column
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->cascadeOnDelete();
            // Re-add the unique index
            $table->unique(['role_id', 'permission_id'], 'roles_permissions_role_id_permission_id_unique');
            // Drop permission_category_id column
            $table->dropForeign(['permission_category_id']);
            $table->dropColumn('permission_category_id');
        });
    }
};
