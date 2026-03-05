<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateMastery implements ShouldQueue
{
    use Queueable;

    /** @param array<int> $skillIds */
    public function __construct(
        public readonly int $studentId,
        public readonly array $skillIds,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $student = \App\Domain\Accounts\Models\Student::find($this->studentId);

        if (! $student || empty($this->skillIds)) {
            return;
        }

        $service = app(\App\Domain\Gameplay\Services\MasteryService::class);

        foreach ($this->skillIds as $skillId) {
            $service->update($student, $skillId, correct: true);
        }
    }
}
