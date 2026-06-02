<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Daily AI commercial agent pass: analyses deals/activities and creates AiSuggestion rows.
 *
 * TODO: integrate with App\Services\AI\CommercialAgent.
 */
class RunCommercialAgentDaily implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(\App\Services\AI\CommercialAgent $agent): void
    {
        $agent->generateDailySuggestions();
    }
}
