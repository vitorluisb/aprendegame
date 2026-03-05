<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateStreak implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $studentId,
    ) {
        $this->onQueue('critical');
    }

    public function handle(): void
    {
        $student = \App\Domain\Accounts\Models\Student::find($this->studentId);

        if (! $student) {
            return;
        }

        app(\App\Domain\Gameplay\Services\StreakService::class)->update($student);
    }
}
