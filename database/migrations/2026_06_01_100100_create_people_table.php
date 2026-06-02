<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
