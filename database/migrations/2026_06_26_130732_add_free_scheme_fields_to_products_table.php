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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_free_eligible')->default(false)->after('brand');
            $table->integer('free_qty_buy')->nullable()->after('is_free_eligible');
            $table->integer('free_qty_get')->nullable()->after('free_qty_buy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_free_eligible', 'free_qty_buy', 'free_qty_get']);
        });
    }
};
