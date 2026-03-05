<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ClassController extends Controller
{
    public function show(SchoolClass $schoolClass): Response
    {
        Gate::authorize('view', $schoolClass);

        return response(['class' => $schoolClass->load('students', 'grade')]);
    }

    public function addStudent(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $request->validate(['student_id' => ['required', 'integer']]);

        // Busca sem o SchoolScope para verificar a escola corretamente na policy
        $student = Student::withoutGlobalScopes()->findOrFail($request->student_id);

        Gate::authorize('addStudent', [$schoolClass, $student]);

        $schoolClass->students()->syncWithoutDetaching([$student->id]);

        return back();
    }
}
