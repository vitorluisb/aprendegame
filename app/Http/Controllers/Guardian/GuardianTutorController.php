<?php

namespace App\Http\Controllers\Guardian;

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GuardianTutorController extends Controller
{
    public function index(Student $student): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $isGuardian = $user->studentsGuarded()
            ->withoutGlobalScopes()
            ->where('students.id', $student->id)
            ->exists();

        if (! $isGuardian) {
            abort(403);
        }

        $messages = TutorMessage::query()
            ->where('student_id', $student->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (TutorMessage $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'blocked' => $message->blocked,
                'blocked_reason' => $message->blocked_reason,
                'created_at' => $message->created_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Guardian/TutorConversations', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'avatar_url' => $student->avatar_url,
            ],
            'messages' => $messages,
        ]);
    }
}
