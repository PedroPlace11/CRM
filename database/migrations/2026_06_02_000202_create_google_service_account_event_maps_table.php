<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('google_service_account_event_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->string('google_event_id')->unique();
            $table->timestamps();

            $table->unique(['calendar_event_id'], 'gsa_map_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_service_account_event_maps');
    }
};
