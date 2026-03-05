<?php

namespace App\Jobs;

use App\Domain\Gameplay\Services\LeagueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecomputeWeeklyLeagues implements ShouldQueue
{
    use Queueable;

    public function handle(LeagueService $service): void
    {
        $service->snapshotAndReset();
        Log::info('Weekly leagues recomputed and reset');
    }
}
