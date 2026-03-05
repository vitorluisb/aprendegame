<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class GuardianDashboardController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $students = $user->studentsGuarded()
            ->withoutGlobalScopes()
            ->with(['streak'])
            ->get(['students.id', 'students.name', 'students.avatar_url']);

        return Inertia::render('Guardian/Dashboard', [
            'students' => $students,
        ]);
    }
}
