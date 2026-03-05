<?php

namespace App\Http\Controllers\Dashboard;

use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class TeacherDashboardController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $classes = SchoolClass::where('school_id', $user->school_id)
            ->where('active', true)
            ->with('grade')
            ->get();

        $classStudentIds = $classes->flatMap(fn ($c) => $c->students()->pluck('students.id'));

        // Alunos em risco: não estudam há mais de 3 dias ou sem streak
        $atRiskStudents = Student::withoutGlobalScopes()
            ->whereIn('id', $classStudentIds)
            ->where(function ($query): void {
                $query->whereHas('streak', function ($q): void {
                    $q->where('last_activity_date', '<', now()->subDays(3));
                })->orWhereDoesntHave('streak');
            })
            ->get(['students.id', 'students.name', 'students.avatar_url']);

        return Inertia::render('Teacher/Dashboard', [
            'classes' => $classes,
            'at_risk_section' => 'alunos em risco',
            'at_risk_students' => $atRiskStudents,
        ]);
    }
}
