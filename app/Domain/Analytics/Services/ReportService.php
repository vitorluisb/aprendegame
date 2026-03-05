<?php

namespace App\Domain\Analytics\Services;

use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ReportService
{
    public function generateClassReport(SchoolClass $schoolClass): \Barryvdh\DomPDF\PDF
    {
        $students = $schoolClass->students()
            ->withoutGlobalScopes()
            ->with(['streak', 'xpTransactions'])
            ->get()
            ->map(fn (Student $student) => $this->buildStudentData($student));

        $data = [
            'schoolClass' => $schoolClass->load('grade', 'school'),
            'students' => $students,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'totalStudents' => $students->count(),
            'averageXp' => $students->count() > 0
                ? (int) round($students->avg('total_xp'))
                : 0,
        ];

        return Pdf::loadView('reports.class', $data)->setPaper('a4');
    }

    /** @return array<string, mixed> */
    private function buildStudentData(Student $student): array
    {
        return [
            'name' => $student->name,
            'total_xp' => $student->totalXp(),
            'current_streak' => $student->streak?->current ?? 0,
            'best_streak' => $student->streak?->best ?? 0,
            'last_activity' => $student->streak?->last_activity_date?->format('d/m/Y') ?? '—',
        ];
    }

    /** @param Collection<int, array<string, mixed>> $students */
    public function buildStudentCollection(Collection $students): Collection
    {
        return $students;
    }
}
