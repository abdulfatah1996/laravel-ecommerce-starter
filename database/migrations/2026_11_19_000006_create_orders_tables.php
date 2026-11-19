<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            // يمنع تكرار الطلب عند الضغط المزدوج على زر الإرسال.
            $table->string('checkout_token', 64)->nullable()->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_method', 30);
            $table->string('payment_status', 20)->default('unpaid');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency_code', 3);
            // لقطة من بيانات الزبون وقت الطلب (لا تتغير إن تغيّر سجل العميل).
            $table->string('customer_name');
            $table->string('customer_phone', 40);
            $table->string('customer_email')->nullable();
            $table->string('city', 100);
            $table->string('address', 255);
            $table->text('notes')->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku', 64)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
