<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ميزات الواجهة الجديدة: خيارات المنتج (مقاس/لون)، مناطق الشحن، واتساب، وربط الطلب بحساب الزبون.
 * إضافية فقط (لا تحذف ولا تعدّل بيانات موجودة) فتصلح للتشغيل على متجر حي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('option_name', 40)->nullable()->after('description');
            $table->string('delivery_note', 255)->nullable()->after('option_name');
            $table->string('preview_3d', 20)->nullable()->after('delivery_note');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label', 80);
            $table->string('sku', 64)->nullable();
            $table->decimal('price', 12, 2)->nullable(); // فارغ = سعر المنتج
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('fee', 10, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('delivery_info', 255)->nullable();
            $table->string('tagline', 120)->nullable();
            $table->unsignedBigInteger('hero_product_id')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
            $table->string('variant_label', 80)->nullable()->after('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->string('shipping_zone', 100)->nullable()->after('shipping_fee');
        });

        // حسابات الزبائن تستخدم جدول users بدور فارغ (لا تدخل لوحة الإدارة).
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('shipping_zone');
        });
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn(['variant_id', 'variant_label']));
        Schema::table('store_settings', fn (Blueprint $t) => $t->dropColumn(['whatsapp_number', 'delivery_info', 'tagline', 'hero_product_id']));
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('product_variants');
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['option_name', 'delivery_note', 'preview_3d']));
    }
};
