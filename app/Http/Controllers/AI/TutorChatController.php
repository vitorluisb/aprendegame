<?php

namespace App\Http\Controllers\AI;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\AI\Models\TutorMessage;
use App\Domain\AI\Services\ModerationService;
use App\Domain\AI\Services\TutorChatService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTutorMessageRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TutorChatController extends Controller
{
    public function __construct(
        private readonly ModerationService $moderationService,
        private readonly TutorChatService $tutorChatService
    ) {}

    public function index(): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $isStudentContext = $user->role === UserRole::Student->value
            || $user->studentProfile()->withoutGlobalScopes()->exists();

        if (! $isStudentContext) {
            return redirect('/dashboard');
        }

        $student = $user->ensureStudentProfile();

        $messages = TutorMessage::query()
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (TutorMessage $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'blocked' => $message->blocked,
                'blocked_reason' => $message->blocked_reason,
                'created_at' => $message->created_at?->format('Y-m-d H:i:s'),
            ]);

        $dailyLimit = $this->moderationService->dailyLimitFor($student);
        $remaining = $this->moderationService->remainingMessages($student);

        return Inertia::render('Tutor/Index', [
            'messages' => $messages,
            'daily_limit' => $dailyLimit,
            'remaining_messages' => $remaining,
        ]);
    }

    public function store(StoreTutorMessageRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();
        $message = trim($request->string('message')->toString());

        if ($this->moderationService->isBlocked($message)) {
            TutorMessage::query()->create([
                'student_id' => $student->id,
                'role' => 'student',
                'content' => $message,
                'blocked' => true,
                'blocked_reason' => $this->moderationService->blockedReason($message),
                'prompt_tokens' => 0,
                'result_tokens' => 0,
            ]);

            return back()->withErrors([
                'tutor' => 'Mensagem bloqueada pela moderação. Reescreva a pergunta com foco no conteúdo escolar.',
            ]);
        }

        if ($this->moderationService->hasReachedDailyLimit($student)) {
            return back()->withErrors([
                'tutor' => 'Você atingiu seu limite diário de mensagens do tutor.',
            ]);
        }

        $this->tutorChatService->answer($student, $message);

        return back();
    }
}
