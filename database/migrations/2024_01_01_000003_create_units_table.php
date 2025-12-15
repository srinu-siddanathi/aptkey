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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('resident_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('block')->nullable();
            $table->string('unit_number');
            $table->enum('type', ['1BHK', '2BHK', '3BHK', '4BHK', 'Penthouse', 'Shop', 'Office'])->default('2BHK');
            $table->decimal('area_sqft', 10, 2)->nullable();
            $table->decimal('monthly_maintenance', 10, 2)->default(0);
            $table->boolean('is_occupied')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('resident_id');
            $table->unique(['tenant_id', 'block', 'unit_number'], 'unique_unit_per_tenant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};

