<?php

use App\Jobs\RecomputeWeeklyLeagues;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recalcula e persiste o ranking semanal todo domingo às 23:59
Schedule::job(new RecomputeWeeklyLeagues)->weeklyOn(0, '23:59');

// Apaga mensagens do tutor com mais de 15 dias
Schedule::call(function (): void {
    \App\Domain\AI\Models\TutorMessage::query()
        ->where('created_at', '<', now()->subDays(15))
        ->delete();
})->daily()->name('tutor-messages-cleanup')->withoutOverlapping();
