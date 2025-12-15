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
        // Check if users table exists (created by Laravel default migration)
        if (Schema::hasTable('users')) {
            // Alter existing table
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->enum('role', ['super_admin', 'apartment_manager', 'resident'])->default('resident')->after('password');
                $table->string('phone')->nullable()->after('role');
                $table->softDeletes()->after('updated_at');
                
                $table->index('tenant_id');
                $table->index('role');
            });
        } else {
            // Create new table if it doesn't exist
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['super_admin', 'apartment_manager', 'resident'])->default('resident');
                $table->string('phone')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();

                $table->index('tenant_id');
                $table->index('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropIndex(['role']);
                $table->dropColumn(['tenant_id', 'role', 'phone', 'deleted_at']);
            });
        }
    }
};

