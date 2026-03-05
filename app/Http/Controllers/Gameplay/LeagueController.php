<?php

namespace App\Http\Controllers\Gameplay;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Services\LeagueService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeagueController extends Controller
{
    public function __construct(private readonly LeagueService $leagueService) {}

    public function index(): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $isStudentContext = $user->role === UserRole::Student->value
            || $user->studentProfile()->withoutGlobalScopes()->exists();

        if (! $isStudentContext) {
            return redirect('/dashboard');
        }

        $student = $user->ensureStudentProfile()->load('grade');

        $leaderboardRaw = $this->leagueService->getLeaderboard($student->school_id, 50);
        $studentIds = array_map(fn (string|int $id): int => (int) $id, array_keys($leaderboardRaw));

        $studentsById = Student::withoutGlobalScopes()
            ->whereIn('id', $studentIds)
            ->get(['id', 'name', 'avatar_url', 'grade_id'])
            ->keyBy('id');
        $equippedAvatarByStudentId = StudentItem::query()
            ->whereIn('student_id', $studentIds)
            ->where('equipped', true)
            ->whereHas('item', fn ($query) => $query->where('type', 'avatar')->whereNotNull('image_url'))
            ->with('item:id,image_url')
            ->get()
            ->keyBy('student_id');

        $filteredStudentIds = array_values(array_filter($studentIds, function (int $studentId) use ($studentsById, $student): bool {
            if (! $student->grade_id) {
                return true;
            }

            return ($studentsById[$studentId]->grade_id ?? null) === $student->grade_id;
        }));

        $entries = [];
        $myRank = null;
        $myXp = 0;

        foreach ($filteredStudentIds as $index => $studentId) {
            $rank = $index + 1;
            $weeklyXp = (int) ($leaderboardRaw[$studentId] ?? $leaderboardRaw[(string) $studentId] ?? 0);
            $isMe = $studentId === $student->id;

            if ($isMe) {
                $myRank = $rank;
                $myXp = $weeklyXp;
            }

            $entries[] = [
                'rank' => $rank,
                'weekly_xp' => $weeklyXp,
                'is_me' => $isMe,
                'student' => [
                    'id' => $studentId,
                    'name' => $studentsById[$studentId]->name ?? "Aluno #{$studentId}",
                    'avatar_url' => $equippedAvatarByStudentId[$studentId]?->item?->image_url
                        ?? $this->normalizeStudentAvatarUrl($studentsById[$studentId]->avatar_url ?? null)
                        ?? null,
                ],
            ];
        }

        if ($myRank === null) {
            $myRank = count($entries) + 1;
            $myXp = 0;
        }

        $myLeague = $myXp > 0
            ? $this->resolveLeague($myRank, max(count($entries), 1))
            : 'bronze';

        return Inertia::render('League/Index', [
            'scope' => $student->school_id ? 'school' : 'global',
            'grade_filter_name' => $student->grade?->name,
            'week' => now()->weekOfYear,
            'year' => now()->year,
            'entries' => $entries,
            'my_position' => [
                'rank' => $myRank,
                'weekly_xp' => $myXp,
                'league' => $myLeague,
            ],
        ]);
    }

    private function resolveLeague(int $rank, int $total): string
    {
        if ($total <= 0) {
            return 'bronze';
        }

        $percentile = $rank / $total;

        return match (true) {
            $rank <= 3 => 'platinum',
            $percentile <= 0.25 => 'gold',
            $percentile <= 0.60 => 'silver',
            default => 'bronze',
        };
    }

    private function normalizeStudentAvatarUrl(?string $avatarUrl): ?string
    {
        if (! $avatarUrl) {
            return null;
        }

        if (str_starts_with($avatarUrl, '/storage/student-avatars/')) {
            return '/media/student-avatars/'.basename($avatarUrl);
        }

        return $avatarUrl;
    }
}
