<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AwardXP implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $studentId,
        public readonly int $amount,
        public readonly string $reason,
        public readonly int $referenceId,
    ) {
        $this->onQueue('critical');
    }

    public function handle(): void
    {
        \App\Domain\Gameplay\Models\XpTransaction::create([
            'student_id' => $this->studentId,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'reference_type' => 'LessonRun',
            'reference_id' => $this->referenceId,
        ]);
    }
}
