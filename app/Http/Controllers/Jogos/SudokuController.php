<?php

namespace App\Http\Controllers\Jogos;

use App\Domain\Sudoku\Models\SudokuPuzzle;
use App\Domain\Sudoku\Models\SudokuSession;
use App\Domain\Sudoku\Services\SudokuService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class SudokuController extends Controller
{
    public function __construct(private readonly SudokuService $sudokuService) {}

    public function lobby(): Response
    {
        return Inertia::render('Sudoku/Lobby');
    }

    public function difficulty(): Response
    {
        $activeByDifficulty = SudokuPuzzle::query()
            ->selectRaw('difficulty, COUNT(*) as total')
            ->where('is_active', true)
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');

        return Inertia::render('Sudoku/Difficulty', [
            'availability' => [
                'easy' => (int) ($activeByDifficulty['easy'] ?? 0),
                'medium' => (int) ($activeByDifficulty['medium'] ?? 0),
                'hard' => (int) ($activeByDifficulty['hard'] ?? 0),
            ],
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
        ]);

        /** @var User $user */
        $user = auth()->user();

        try {
            $data = $this->sudokuService->startGameSession($user, (string) $payload['difficulty']);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('sudoku.difficulty')
                ->withErrors(['difficulty' => $exception->getMessage()]);
        }

        return redirect()->route('sudoku.play', ['session' => $data['session']['id']]);
    }

    public function play(SudokuSession $session): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ((int) $session->user_id !== (int) $user->id) {
            return redirect()->route('sudoku.lobby');
        }

        if ($session->status !== 'active') {
            return redirect()->route('sudoku.result', ['session' => $session->id]);
        }

        $payload = $this->sudokuService->buildSessionPayload($session);

        return Inertia::render('Sudoku/Play', [
            'session' => $payload['session'],
            'puzzle' => $payload['puzzle'],
        ]);
    }

    public function submitMove(Request $request, SudokuSession $session): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ((int) $session->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Sessão inválida.'], 403);
        }

        $payload = $request->validate([
            'row_index' => ['required', 'integer', 'min:0', 'max:8'],
            'col_index' => ['required', 'integer', 'min:0', 'max:8'],
            'value' => ['nullable', 'integer', 'min:1', 'max:9'],
        ]);

        try {
            $value = null;

            if (array_key_exists('value', $payload) && $payload['value'] !== null) {
                $value = (int) $payload['value'];
            }

            $result = $this->sudokuService->submitMove(
                $session,
                (int) $payload['row_index'],
                (int) $payload['col_index'],
                $value,
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'result' => $result,
            'redirect' => $result['completed'] ? route('sudoku.result', ['session' => $session->id]) : null,
        ]);
    }

    public function result(SudokuSession $session): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ((int) $session->user_id !== (int) $user->id) {
            return redirect()->route('sudoku.lobby');
        }

        if ($session->status === 'active') {
            return redirect()->route('sudoku.play', ['session' => $session->id]);
        }

        $payload = $this->sudokuService->buildSessionPayload($session);

        return Inertia::render('Sudoku/Result', [
            'session' => $payload['session'],
        ]);
    }
}
