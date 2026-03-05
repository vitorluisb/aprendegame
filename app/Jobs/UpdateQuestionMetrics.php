<?php

namespace App\Jobs;

use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateQuestionMetrics implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $questionId,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $question = Question::find($this->questionId);

        if (!$question) {
            return;
        }

        $stats = Attempt::where('question_id', $this->questionId)
            ->selectRaw('COUNT(*) as total, AVG(time_ms) as avg_time, SUM(CASE WHEN correct = 0 THEN 1 ELSE 0 END) as errors')
            ->first();

        if (!$stats || $stats->total === 0) {
            return;
        }

        $question->update([
            'avg_time_ms' => (int) $stats->avg_time,
            'error_rate' => round($stats->errors / $stats->total, 4),
        ]);
    }
}
