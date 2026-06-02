<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('stage_id')->constrained('deal_stages')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('value', 14, 2)->default(0);
            $table->unsignedTinyInteger('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage_id', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
