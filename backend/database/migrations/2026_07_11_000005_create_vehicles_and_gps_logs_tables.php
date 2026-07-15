<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('plate_number')->unique();
            $table->string('model')->nullable();
            $table->uuid('assigned_user_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('gps_engine_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('vehicle_id');
            $table->boolean('engine_status'); // true = ON, false = OFF
            $table->timestamp('event_time');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Method A & B Tracking
            $table->enum('source_method', ['excel_import', 'web_scraping', 'api_direct'])->default('excel_import');
            $table->string('source_reference')->nullable(); // Filename for Excel or URL for Scraping
            $table->json('raw_data')->nullable(); // Store original payload for debugging
            
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('vehicle_id');
            $table->decimal('amount', 15, 2);
            $table->decimal('liters', 10, 2);
            $table->timestamp('refuel_time');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
        Schema::dropIfExists('gps_engine_logs');
        Schema::dropIfExists('vehicles');
    }
};
