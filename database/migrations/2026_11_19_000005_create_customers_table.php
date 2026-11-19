<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // العملاء يُنشأون تلقائيًا عند الطلب (لا توجد حسابات دخول للزبائن).
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 40)->unique();
            $table->string('email')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
