<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable()->index();
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('deal_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['deal_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_products');
        Schema::dropIfExists('products');
    }
};
