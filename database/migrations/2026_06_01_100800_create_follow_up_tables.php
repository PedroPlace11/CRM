<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('follow_up_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('follow_up_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('active'); // active | paused | stopped | completed
            $table->unsignedInteger('sent_count')->default(0);
            $table->json('used_template_ids')->nullable();
            $table->timestamp('next_send_at')->nullable()->index();
            $table->timestamp('stopped_at')->nullable();
            $table->string('stop_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_sequences');
        Schema::dropIfExists('follow_up_templates');
    }
};
