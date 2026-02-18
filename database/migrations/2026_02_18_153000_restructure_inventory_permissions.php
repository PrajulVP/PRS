<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename existing 'Orders' to 'orders_temp' to free up the unique name
        $oldOrdersGroup = PermissionGroup::where('name', 'Orders')->first();

        if ($oldOrdersGroup) {
            $oldOrdersGroup->name = 'orders_temp';
            $oldOrdersGroup->save();

            // 2. Create the new 'Orders' group (which will get the higher ID)
            $newOrdersGroup = PermissionGroup::create([
                'name' => 'Orders',
                'is_active' => 1,
                'system' => 0
            ]);

            // 3. Move categories from old (temp) group to new 'Orders' group
            PermissionCategory::where('perm_group_id', $oldOrdersGroup->id)->update(['perm_group_id' => $newOrdersGroup->id]);

            // 4. Rename the old (temp) group to 'Inventory'
            $oldOrdersGroup->name = 'Inventory';
            $oldOrdersGroup->save();

            // 5. Move 'Inventory' category to the new 'Inventory' group
            $inventoryCat = PermissionCategory::where('short_code', 'inventory')->first();
            if ($inventoryCat) {
                // Ensure the category uses the new 'Inventory' group ID
                $inventoryCat->perm_group_id = $oldOrdersGroup->id;
                $inventoryCat->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
