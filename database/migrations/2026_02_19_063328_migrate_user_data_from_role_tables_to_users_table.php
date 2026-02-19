<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure contact_no column exists in users table (it was in fillable but maybe not in DB)
        if (!Schema::hasColumn('users', 'contact_no')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('contact_no')->nullable();
            });
        }

        // 2. Migrate data from role tables to users table
        $this->migrateRoleData('distributors');
        $this->migrateRoleData('sales_managers');
        $this->migrateRoleData('fieldstaffs');
        $this->migrateRoleData('retailers');
    }

    private function migrateRoleData($tableName)
    {
        // Get all records from the role table that have a user_id
        $records = DB::table($tableName)->whereNotNull('user_id')->get();

        foreach ($records as $record) {
            $user = DB::table('users')->where('id', $record->user_id)->first();

            if ($user) {
                // Prepare update data
                $updateData = [];

                // Only update if the user field is empty/null to preserve any manual updates done recently
                if (empty($user->address) && !empty($record->address)) {
                    $updateData['address'] = $record->address;
                }

                // Assuming role tables have 'pincode' (SalesManager might not, check field existence)
                if (Schema::hasColumn($tableName, 'pincode')) {
                    if (empty($user->pincode) && !empty($record->pincode)) {
                        $updateData['pincode'] = $record->pincode;
                    }
                }

                // Check for city/district -> city mapping if applicable. 
                // Simple mapping: If role table has city, map it. Otherwise, maybe default or leave blank.
                // Most role tables don't seem to have 'city', but might have 'district_id'.
                // For now, we will just migrate direct matches effectively.

                if (empty($user->contact_no) && !empty($record->contact_no)) {
                    $updateData['contact_no'] = $record->contact_no;
                }

                if (!empty($updateData)) {
                    DB::table('users')->where('id', $record->user_id)->update($updateData);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't reverse data migrations of this sort (destructive to remove data).
        // But if we added the contact_no column, we could drop it.
        // However, since it's likely intended to be permanent, we can leave down empty or just conditionally drop.
        if (Schema::hasColumn('users', 'contact_no')) {
            // Schema::table('users', function (Blueprint $table) {
            //    $table->dropColumn('contact_no');
            // });
        }
    }
};
