<?php

use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Domain\Analytics\Services\ReportService;
use App\Domain\Gameplay\Models\XpTransaction;
use Barryvdh\DomPDF\PDF;

it('generates a pdf for a class', function () {
    $class = SchoolClass::factory()->create();

    $service = app(ReportService::class);
    $pdf = $service->generateClassReport($class);

    expect($pdf)->toBeInstanceOf(PDF::class);
});

it('pdf output is non-empty', function () {
    $class = SchoolClass::factory()->create();

    $service = app(ReportService::class);
    $output = $service->generateClassReport($class)->output();

    expect(strlen($output))->toBeGreaterThan(0);
});

it('report includes class name in html', function () {
    $class = SchoolClass::factory()->create(['name' => 'Turma Alpha']);

    $html = view('reports.class', [
        'schoolClass' => $class->load('grade', 'school'),
        'students' => collect(),
        'generatedAt' => now()->format('d/m/Y H:i'),
        'totalStudents' => 0,
        'averageXp' => 0,
    ])->render();

    expect($html)->toContain('Turma Alpha');
});

it('report includes student data', function () {
    $class = SchoolClass::factory()->create();
    $student = Student::factory()->create(['school_id' => $class->school_id, 'name' => 'Maria Silva']);
    $class->students()->attach($student->id, ['enrolled_at' => now()]);

    XpTransaction::factory()->create(['student_id' => $student->id, 'amount' => 150]);

    $service = app(ReportService::class);
    $pdf = $service->generateClassReport($class);

    expect($pdf)->toBeInstanceOf(PDF::class);

    $html = view('reports.class', [
        'schoolClass' => $class->load('grade', 'school'),
        'students' => collect([[
            'name' => 'Maria Silva',
            'total_xp' => 150,
            'current_streak' => 0,
            'best_streak' => 0,
            'last_activity' => '—',
        ]]),
        'generatedAt' => now()->format('d/m/Y H:i'),
        'totalStudents' => 1,
        'averageXp' => 150,
    ])->render();

    expect($html)->toContain('Maria Silva');
    expect($html)->toContain('150');
});

it('report shows empty state when no students', function () {
    $class = SchoolClass::factory()->create();

    $html = view('reports.class', [
        'schoolClass' => $class->load('grade', 'school'),
        'students' => collect(),
        'generatedAt' => now()->format('d/m/Y H:i'),
        'totalStudents' => 0,
        'averageXp' => 0,
    ])->render();

    expect($html)->toContain('Nenhum aluno matriculado.');
});

it('average xp is calculated correctly across students', function () {
    $class = SchoolClass::factory()->create();
    $s1 = Student::factory()->create(['school_id' => $class->school_id]);
    $s2 = Student::factory()->create(['school_id' => $class->school_id]);
    $class->students()->attach([$s1->id => ['enrolled_at' => now()], $s2->id => ['enrolled_at' => now()]]);

    XpTransaction::factory()->create(['student_id' => $s1->id, 'amount' => 100]);
    XpTransaction::factory()->create(['student_id' => $s2->id, 'amount' => 200]);

    $service = app(ReportService::class);
    $pdf = $service->generateClassReport($class);

    expect($pdf)->toBeInstanceOf(PDF::class);
});
