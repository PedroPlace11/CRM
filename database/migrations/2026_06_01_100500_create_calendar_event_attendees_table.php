<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendar_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->morphs('attendee'); // user / person
            $table->string('response')->default('pending');
            $table->timestamps();

            $table->unique(['calendar_event_id', 'attendee_type', 'attendee_id'], 'cea_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_attendees');
    }
};
