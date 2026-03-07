<?php

namespace App\Http\Controllers\Jogos;

use App\Domain\QuizMestre\Models\GkSession;
use App\Domain\QuizMestre\Services\QuizMestreService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizMestreController extends Controller
{
    public function __construct(private readonly QuizMestreService $quizMestreService) {}

    public function lobby(): Response
    {
        return Inertia::render('QuizMestre/Lobby', [
            'max_rounds' => QuizMestreService::MAX_ROUNDS,
        ]);
    }

    public function start(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $session = $this->quizMestreService->startGameSession($user);

        return redirect()->route('quiz-mestre.play', ['session' => $session->id]);
    }

    public function play(GkSession $session): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ((int) $session->user_id !== (int) $user->id) {
            return redirect()->route('quiz-mestre.lobby');
        }

        if ($session->status !== 'active') {
            return redirect()->route('quiz-mestre.result', ['session' => $session->id]);
        }

        $question = $this->quizMestreService->loadNextQuestion($session);

        if ($question === null) {
            $this->quizMestreService->finishGameSession($session->refresh());

            return redirect()->route('quiz-mestre.result', ['session' => $session->id]);
        }

        return Inertia::render('QuizMestre/Play', [
            'session' => [
                'id' => $session->id,
                'score' => $session->score,
                'current_round' => $session->current_round,
                'reward_xp' => $session->reward_xp,
                'reward_gems' => $session->reward_gems,
                'status' => $session->status,
                'max_rounds' => QuizMestreService::MAX_ROUNDS,
            ],
            'question' => $question,
        ]);
    }

    public function submit(Request $request, GkSession $session): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ((int) $session->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Sessão inválida.'], 403);
        }

        $payload = $request->validate([
            'question_id' => ['required', 'integer', 'exists:gk_questions,id'],
            'selected_option' => ['required', 'string', 'in:A,B,C,D,a,b,c,d'],
            'response_time_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $result = $this->quizMestreService->submitAnswer(
            $session,
            (int) $payload['question_id'],
            (string) $payload['selected_option'],
            (int) ($payload['response_time_ms'] ?? 0),
        );

        if ($result['status'] !== 'active') {
            return response()->json([
                'result' => $result,
                'next_question' => null,
                'finished' => true,
                'redirect' => route('quiz-mestre.result', ['session' => $session->id]),
            ]);
        }

        $nextQuestion = $this->quizMestreService->loadNextQuestion($session->refresh());

        return response()->json([
            'result' => $result,
            'next_question' => $nextQuestion,
            'finished' => $nextQuestion === null,
            'redirect' => $nextQuestion === null ? route('quiz-mestre.result', ['session' => $session->id]) : null,
        ]);
    }

    public function result(GkSession $session): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ((int) $session->user_id !== (int) $user->id) {
            return redirect()->route('quiz-mestre.lobby');
        }

        if ($session->status === 'active') {
            return redirect()->route('quiz-mestre.play', ['session' => $session->id]);
        }

        return Inertia::render('QuizMestre/Result', [
            'session' => [
                'id' => $session->id,
                'score' => $session->score,
                'current_round' => $session->current_round,
                'correct_answers' => $session->correct_answers,
                'reward_xp' => $session->reward_xp,
                'reward_gems' => $session->reward_gems,
                'status' => $session->status,
                'max_rounds' => QuizMestreService::MAX_ROUNDS,
                'finished_at' => $session->finished_at?->toIso8601String(),
            ],
        ]);
    }
}
