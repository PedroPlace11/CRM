<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Lightweight quick-create timeline activity (separate from spatie activitylog).
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject'); // deal / entity / person
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // call | task | meeting | note | email | stage_change | system
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('happened_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
