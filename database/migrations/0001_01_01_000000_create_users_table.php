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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED (Primary Key)
            $table->string('name', 100); // VARCHAR(100)
            $table->string('email', 150)->unique(); // VARCHAR(150) UNIQUE
            $table->string('contact_no', 20)->unique()->nullable(); // VARCHAR(20) UNIQUE
            $table->string('address', 255)->nullable(); // VARCHAR(255) NULL
            $table->string('pincode')->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('area_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('profile_pic')->nullable();
            $table->timestamp('email_verified_at')->nullable(); // TIMESTAMP NULL
            $table->string('password', 255); // VARCHAR(255)
            $table->rememberToken(); // VARCHAR(100) NULL (for "remember me" sessions)
            $table->string('role', 20)->default('user');
            $table->timestamps(); // created_at, updated_at (TIMESTAMP)
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary(); // VARCHAR(150) PRIMARY KEY
            $table->string('token', 255); // VARCHAR(255)
            $table->timestamp('created_at')->nullable(); // TIMESTAMP NULL
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary(); // VARCHAR(191)            
            $table->foreignId('user_id')->nullable()->index(); // BIGINT UNSIGNED INDEX
            $table->string('ip_address', 45)->nullable(); // VARCHAR(45) NULL (IPv4/IPv6)
            $table->text('user_agent')->nullable(); // TEXT NULL
            $table->longText('payload'); // LONGTEXT (session data)
            $table->integer('last_activity')->index(); // INT INDEX (timestamp)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
