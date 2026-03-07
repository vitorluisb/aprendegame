<?php

namespace App\Domain\Sudoku\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Domain\Gameplay\Services\LeagueService;
use App\Domain\Sudoku\Models\SudokuMove;
use App\Domain\Sudoku\Models\SudokuPuzzle;
use App\Domain\Sudoku\Models\SudokuSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SudokuService
{
    /**
     * @return array{session:array{id:int,status:string,difficulty:string,started_at:string,elapsed_seconds:int,mistakes_count:int,hints_used:int,reward_xp:int,reward_gems:int},puzzle:array{given_cells:array<int,array<int,int>>,filled_cells:array<int,array<int,int>>,fixed_positions:array<int,array<int,bool>>,size:int,block_size:int}}
     */
    public function startGameSession(User $user, string $difficulty): array
    {
        $difficulty = strtolower(trim($difficulty));

        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw new RuntimeException('Dificuldade inválida.');
        }

        /** @var SudokuPuzzle|null $puzzle */
        $puzzle = SudokuPuzzle::query()
            ->where('is_active', true)
            ->where('difficulty', $difficulty)
            ->inRandomOrder()
            ->first();

        if (! $puzzle) {
            throw new RuntimeException('Não há puzzle ativo para essa dificuldade.');
        }

        $session = SudokuSession::query()->create([
            'user_id' => $user->id,
            'puzzle_id' => $puzzle->id,
            'status' => 'active',
            'started_at' => now(),
            'elapsed_seconds' => 0,
            'mistakes_count' => 0,
            'hints_used' => 0,
            'reward_xp' => 0,
            'reward_gems' => 0,
        ]);

        return $this->buildSessionPayload($session->load('puzzle', 'moves'));
    }

    /**
     * @return array{session:array{id:int,status:string,difficulty:string,started_at:string,elapsed_seconds:int,mistakes_count:int,hints_used:int,reward_xp:int,reward_gems:int},puzzle:array{given_cells:array<int,array<int,int>>,filled_cells:array<int,array<int,int>>,fixed_positions:array<int,array<int,bool>>,size:int,block_size:int}}
     */
    public function buildSessionPayload(SudokuSession $session): array
    {
        $session->loadMissing(['puzzle', 'moves']);

        /** @var SudokuPuzzle $puzzle */
        $puzzle = $session->puzzle;
        $givenCells = $this->stringToBoard($puzzle->puzzle_string);
        $filledCells = $givenCells;
        $fixedPositions = [];

        for ($row = 0; $row < 9; $row++) {
            $fixedPositions[$row] = [];

            for ($col = 0; $col < 9; $col++) {
                $fixedPositions[$row][$col] = $givenCells[$row][$col] > 0;
            }
        }

        foreach ($session->moves as $move) {
            if (! $fixedPositions[$move->row_index][$move->col_index] && $move->value > 0) {
                $filledCells[$move->row_index][$move->col_index] = $move->value;
            }
        }

        return [
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'difficulty' => (string) $puzzle->difficulty,
                'started_at' => $session->started_at?->toIso8601String() ?? now()->toIso8601String(),
                'elapsed_seconds' => $session->elapsed_seconds,
                'mistakes_count' => $session->mistakes_count,
                'hints_used' => $session->hints_used,
                'reward_xp' => $session->reward_xp,
                'reward_gems' => $session->reward_gems,
            ],
            'puzzle' => [
                'given_cells' => $givenCells,
                'filled_cells' => $filledCells,
                'fixed_positions' => $fixedPositions,
                'size' => 9,
                'block_size' => 3,
            ],
        ];
    }

    /**
     * @return array{correct:bool,completed:bool,session:array{id:int,status:string,difficulty:string,started_at:string,elapsed_seconds:int,mistakes_count:int,hints_used:int,reward_xp:int,reward_gems:int},puzzle:array{given_cells:array<int,array<int,int>>,filled_cells:array<int,array<int,int>>,fixed_positions:array<int,array<int,bool>>,size:int,block_size:int},message:string}
     */
    public function submitMove(SudokuSession $session, int $rowIndex, int $colIndex, ?int $value): array
    {
        if ($session->status !== 'active') {
            throw new RuntimeException('Sessão finalizada.');
        }

        if ($rowIndex < 0 || $rowIndex > 8 || $colIndex < 0 || $colIndex > 8) {
            throw new RuntimeException('Posição inválida.');
        }

        if ($value !== null && ($value < 1 || $value > 9)) {
            throw new RuntimeException('Valor inválido.');
        }

        $session->loadMissing(['puzzle', 'moves']);
        $puzzle = $session->puzzle;

        if ($puzzle === null) {
            throw new RuntimeException('Puzzle não encontrado.');
        }

        $puzzleBoard = $this->stringToBoard($puzzle->puzzle_string);

        if ($puzzleBoard[$rowIndex][$colIndex] > 0) {
            throw new RuntimeException('Essa célula é fixa e não pode ser alterada.');
        }

        if ($value === null) {
            SudokuMove::query()
                ->where('session_id', $session->id)
                ->where('row_index', $rowIndex)
                ->where('col_index', $colIndex)
                ->delete();

            $session->update([
                'elapsed_seconds' => $this->elapsedSeconds($session),
            ]);

            $payload = $this->buildSessionPayload($session->refresh());

            return [
                'correct' => true,
                'completed' => false,
                'session' => $payload['session'],
                'puzzle' => $payload['puzzle'],
                'message' => 'Célula limpa.',
            ];
        }

        $solutionBoard = $this->stringToBoard($puzzle->solution_string);
        $isCorrect = $solutionBoard[$rowIndex][$colIndex] === $value;

        SudokuMove::query()->updateOrCreate(
            [
                'session_id' => $session->id,
                'row_index' => $rowIndex,
                'col_index' => $colIndex,
            ],
            [
                'value' => $value,
                'is_correct' => $isCorrect,
            ],
        );

        if (! $isCorrect) {
            $session->update([
                'mistakes_count' => $session->mistakes_count + 1,
                'elapsed_seconds' => $this->elapsedSeconds($session),
            ]);

            $payload = $this->buildSessionPayload($session->refresh());

            return [
                'correct' => false,
                'completed' => false,
                'session' => $payload['session'],
                'puzzle' => $payload['puzzle'],
                'message' => 'Número incorreto para esta posição.',
            ];
        }

        $session->update([
            'elapsed_seconds' => $this->elapsedSeconds($session),
        ]);

        $session = $session->refresh()->load('moves', 'puzzle');

        if (! $this->isCompleted($session)) {
            $payload = $this->buildSessionPayload($session);

            return [
                'correct' => true,
                'completed' => false,
                'session' => $payload['session'],
                'puzzle' => $payload['puzzle'],
                'message' => 'Boa jogada.',
            ];
        }

        $finishedSession = $this->finishGameSession($session);
        $payload = $this->buildSessionPayload($finishedSession->load('puzzle', 'moves'));

        return [
            'correct' => true,
            'completed' => true,
            'session' => $payload['session'],
            'puzzle' => $payload['puzzle'],
            'message' => 'Sudoku concluído com sucesso!',
        ];
    }

    public function finishGameSession(SudokuSession $session): SudokuSession
    {
        if ($session->status === 'completed') {
            return $session;
        }

        $elapsedSeconds = $this->elapsedSeconds($session);
        $rewardXp = $this->calculateXp($session, $elapsedSeconds);
        $rewardGems = $this->calculateGems($session, $elapsedSeconds);

        $session->update([
            'status' => 'completed',
            'finished_at' => now(),
            'elapsed_seconds' => $elapsedSeconds,
            'reward_xp' => $rewardXp,
            'reward_gems' => $rewardGems,
        ]);

        $session->refresh();

        if ($rewardXp <= 0 && $rewardGems <= 0) {
            return $session;
        }

        $alreadyRewarded = XpTransaction::query()
            ->where('reference_type', SudokuSession::class)
            ->where('reference_id', $session->id)
            ->exists();

        if ($alreadyRewarded) {
            return $session;
        }

        DB::transaction(function () use ($session): void {
            /** @var User $user */
            $user = $session->user()->firstOrFail();
            /** @var Student $student */
            $student = $user->ensureStudentProfile();

            if ($session->reward_xp > 0) {
                XpTransaction::query()->create([
                    'student_id' => $student->id,
                    'amount' => $session->reward_xp,
                    'reason' => 'sudoku',
                    'reference_type' => SudokuSession::class,
                    'reference_id' => $session->id,
                    'created_at' => now(),
                ]);

                app(LeagueService::class)->addXP($student, $session->reward_xp);
            }

            if ($session->reward_gems > 0) {
                GemTransaction::query()->create([
                    'student_id' => $student->id,
                    'amount' => $session->reward_gems,
                    'source' => 'sudoku',
                    'sourceable_type' => SudokuSession::class,
                    'sourceable_id' => $session->id,
                    'created_at' => now(),
                ]);
            }
        });

        return $session;
    }

    private function elapsedSeconds(SudokuSession $session): int
    {
        if ($session->started_at === null) {
            return $session->elapsed_seconds;
        }

        return max(0, now()->diffInSeconds($session->started_at));
    }

    private function calculateXp(SudokuSession $session, int $elapsedSeconds): int
    {
        $difficulty = (string) ($session->puzzle?->difficulty ?? 'easy');

        $base = match ($difficulty) {
            'easy' => 35,
            'medium' => 55,
            'hard' => 85,
            default => 35,
        };

        $timeBonus = match (true) {
            $elapsedSeconds <= 600 => 20,
            $elapsedSeconds <= 1200 => 10,
            $elapsedSeconds <= 1800 => 5,
            default => 0,
        };

        $mistakePenalty = min(40, $session->mistakes_count * 4);

        return max(10, $base + $timeBonus - $mistakePenalty);
    }

    private function calculateGems(SudokuSession $session, int $elapsedSeconds): int
    {
        $difficulty = (string) ($session->puzzle?->difficulty ?? 'easy');

        $base = match ($difficulty) {
            'easy' => 1,
            'medium' => 2,
            'hard' => 4,
            default => 1,
        };

        $bonus = $elapsedSeconds <= 900 ? 1 : 0;

        return max(0, $base + $bonus - min(2, intdiv($session->mistakes_count, 5)));
    }

    private function isCompleted(SudokuSession $session): bool
    {
        $session->loadMissing(['puzzle', 'moves']);

        $puzzle = $session->puzzle;

        if (! $puzzle) {
            return false;
        }

        $board = $this->stringToBoard($puzzle->puzzle_string);

        foreach ($session->moves as $move) {
            $board[$move->row_index][$move->col_index] = $move->value;
        }

        $solution = $this->stringToBoard($puzzle->solution_string);

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($board[$row][$col] !== $solution[$row][$col]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function stringToBoard(string $line): array
    {
        if (strlen($line) !== 81) {
            throw new RuntimeException('Puzzle inválido.');
        }

        $chars = str_split($line);
        $board = [];

        for ($row = 0; $row < 9; $row++) {
            $board[$row] = [];

            for ($col = 0; $col < 9; $col++) {
                $board[$row][$col] = (int) $chars[($row * 9) + $col];
            }
        }

        return $board;
    }
}
