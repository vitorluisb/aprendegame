<?php

namespace App\Http\Controllers\Guardian;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\CreateStudentAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class GuardianStudentCreateController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Guardian/CreateStudent');
    }

    public function store(CreateStudentAccountRequest $request): RedirectResponse
    {
        /** @var User $guardian */
        $guardian = auth()->user();

        $studentUser = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => UserRole::Student->value,
            'school_id' => $guardian->school_id,
            'email_verified_at' => now(),
        ]);

        $student = Student::withoutGlobalScopes()->create([
            'user_id' => $studentUser->id,
            'school_id' => $guardian->school_id,
            'name' => $studentUser->name,
            'birth_date' => $request->input('birth_date'),
            'lives_current' => Student::DEFAULT_LIVES,
            'lives_max' => Student::DEFAULT_LIVES,
            'lives_refilled_at' => now(),
        ]);

        $guardian->studentsGuarded()->attach($student->id, [
            'relationship' => 'parent',
            'consent_given' => true,
            'consent_given_at' => now(),
        ]);

        return redirect()->route('guardian.student.show', $student)
            ->with('success', 'Conta do filho criada com sucesso!');
    }
}
