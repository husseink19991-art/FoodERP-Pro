<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name'); // e.g., Box, Piece
            $table->string('short_name'); // e.g., bx, pc
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->uuid('brand_id')->nullable()->after('category_id');
            $table->uuid('base_unit_id')->nullable()->after('brand_id');
            $table->string('barcode')->nullable()->unique()->after('sku');
            $table->string('qrcode')->nullable()->after('barcode');
            
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('base_unit_id')->references('id')->on('units')->onDelete('set null');
        });

        Schema::create('product_unit_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id');
            $table->uuid('from_unit_id');
            $table->uuid('to_unit_id');
            $table->decimal('conversion_factor', 15, 4); // e.g., 1 Box = 12 Pieces
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('from_unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('to_unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_unit_conversions');
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['base_unit_id']);
            $table->dropColumn(['brand_id', 'base_unit_id', 'barcode', 'qrcode']);
        });
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
    }
};
