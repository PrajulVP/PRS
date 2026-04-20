<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('device_uuid')->nullable()->after('player_id');
            $table->timestamp('device_bound_at')->nullable()->after('device_uuid');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_uuid', 'device_bound_at']);
        });
    }
};
