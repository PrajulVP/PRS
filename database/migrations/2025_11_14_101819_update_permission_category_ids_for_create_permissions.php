<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')->where('name', 'create managers')->update(['permission_category_id' => 1]); // Managers
        DB::table('permissions')->where('name', 'create distributors')->update(['permission_category_id' => 2]); // Distributors
        DB::table('permissions')->where('name', 'create fieldstaff')->update(['permission_category_id' => 3]); // Field Staff
        DB::table('permissions')->where('name', 'create retailers')->update(['permission_category_id' => 4]); // Retailers
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->where('name', 'create managers')->update(['permission_category_id' => null]);
        DB::table('permissions')->where('name', 'create distributors')->update(['permission_category_id' => null]);
        DB::table('permissions')->where('name', 'create fieldstaff')->update(['permission_category_id' => null]);
        DB::table('permissions')->where('name', 'create retailers')->update(['permission_category_id' => null]);
    }
};
