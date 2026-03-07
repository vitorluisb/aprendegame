<?php

namespace App\Filament\Widgets;

use App\Domain\Gameplay\Models\Question;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class QuestionsStats extends StatsOverviewWidget
{
    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $stats = [
            Stat::make('Total de Questões', number_format(Question::query()->count(), 0, ',', '.')),
            Stat::make('Geradas Hoje', number_format(Question::query()->whereDate('created_at', today())->count(), 0, ',', '.')),
        ];

        $grouped = Question::query()
            ->join('bncc_skills', 'bncc_skills.id', '=', 'questions.skill_id')
            ->join('grades', 'grades.id', '=', 'bncc_skills.grade_id')
            ->join('subjects', 'subjects.id', '=', 'bncc_skills.subject_id')
            ->select([
                'grades.code as grade_code',
                'subjects.name as subject_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('MIN(grades.`order`) as grade_order'),
            ])
            ->groupBy('grades.code', 'subjects.name')
            ->orderBy('grade_order')
            ->orderBy('subject_name')
            ->get();

        foreach ($grouped as $row) {
            $stats[] = Stat::make(
                "{$row->subject_name} {$row->grade_code}",
                number_format((int) $row->total, 0, ',', '.')
            );
        }

        return $stats;
    }
}
