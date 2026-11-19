<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // صف واحد فقط يحمل إعدادات المتجر.
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->string('timezone', 64)->default('UTC');
            $table->string('locale', 5)->default('ar');
            $table->string('primary_color', 7)->default('#0f766e');
            $table->string('secondary_color', 7)->default('#e7e5e4');
            $table->string('accent_color', 7)->default('#f59e0b');
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->boolean('cod_enabled')->default(true);
            $table->boolean('manual_payment_enabled')->default(false);
            $table->string('manual_payment_label', 100)->nullable();
            $table->text('manual_payment_instructions')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
