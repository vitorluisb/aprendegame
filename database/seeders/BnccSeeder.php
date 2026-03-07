<?php

namespace Database\Seeders;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BnccSeeder extends Seeder
{
    public function run(): void
    {
        $grades = $this->seedGrades();
        $subjects = $this->seedSubjects();
        $this->seedPaths($grades, $subjects);
        $this->seedSkillsFromDocument($grades, $subjects);
    }

    /** @return array<string, Grade> */
    private function seedGrades(): array
    {
        // BNCC cobre do 3º ano do EF até o 3º ano do EM
        $data = [
            // Anos Iniciais do Ensino Fundamental
            ['name' => '3º Ano EF', 'code' => 'EF03', 'stage' => 'fundamental_1', 'order' => 1],
            ['name' => '4º Ano EF', 'code' => 'EF04', 'stage' => 'fundamental_1', 'order' => 2],
            ['name' => '5º Ano EF', 'code' => 'EF05', 'stage' => 'fundamental_1', 'order' => 3],
            // Anos Finais do Ensino Fundamental
            ['name' => '6º Ano EF', 'code' => 'EF06', 'stage' => 'fundamental_2', 'order' => 4],
            ['name' => '7º Ano EF', 'code' => 'EF07', 'stage' => 'fundamental_2', 'order' => 5],
            ['name' => '8º Ano EF', 'code' => 'EF08', 'stage' => 'fundamental_2', 'order' => 6],
            ['name' => '9º Ano EF', 'code' => 'EF09', 'stage' => 'fundamental_2', 'order' => 7],
            // Ensino Médio
            ['name' => '1º Ano EM', 'code' => 'EM01', 'stage' => 'medio', 'order' => 8],
            ['name' => '2º Ano EM', 'code' => 'EM02', 'stage' => 'medio', 'order' => 9],
            ['name' => '3º Ano EM', 'code' => 'EM03', 'stage' => 'medio', 'order' => 10],
        ];

        $grades = [];
        foreach ($data as $item) {
            $grades[$item['code']] = Grade::updateOrCreate(['code' => $item['code']], $item);
        }

        return $grades;
    }

    /** @return array<string, Subject> */
    private function seedSubjects(): array
    {
        $data = [
            // Linguagens
            ['name' => 'Língua Portuguesa', 'slug' => 'portugues',      'icon' => 'book-open',   'color' => '#EF4444'],
            ['name' => 'Arte',               'slug' => 'arte',           'icon' => 'palette',     'color' => '#A855F7'],
            ['name' => 'Inglês',             'slug' => 'ingles',         'icon' => 'languages',   'color' => '#F97316'],
            ['name' => 'Educação Física',    'slug' => 'educacao-fisica', 'icon' => 'activity',    'color' => '#06B6D4'],
            // Matemática
            ['name' => 'Matemática',         'slug' => 'matematica',     'icon' => 'calculator',  'color' => '#3B82F6'],
            // Ciências da Natureza
            ['name' => 'Ciências',           'slug' => 'ciencias',       'icon' => 'flask',       'color' => '#10B981'],
            ['name' => 'Biologia',           'slug' => 'biologia',       'icon' => 'leaf',        'color' => '#22C55E'],
            ['name' => 'Física',             'slug' => 'fisica',         'icon' => 'zap',         'color' => '#06B6D4'],
            ['name' => 'Química',            'slug' => 'quimica',        'icon' => 'atom',        'color' => '#EC4899'],
            // Ciências Humanas
            ['name' => 'História',           'slug' => 'historia',       'icon' => 'landmark',    'color' => '#F59E0B'],
            ['name' => 'Geografia',          'slug' => 'geografia',      'icon' => 'globe',       'color' => '#8B5CF6'],
            ['name' => 'Filosofia',          'slug' => 'filosofia',      'icon' => 'lightbulb',   'color' => '#6366F1'],
            ['name' => 'Sociologia',         'slug' => 'sociologia',     'icon' => 'users',       'color' => '#84CC16'],
        ];

        $subjects = [];
        foreach ($data as $item) {
            $subjects[$item['slug']] = Subject::updateOrCreate(['slug' => $item['slug']], $item);
        }

        return $subjects;
    }

    /**
     * @param  array<string, Grade>  $grades
     * @param  array<string, Subject>  $subjects
     */
    private function seedPaths(array $grades, array $subjects): void
    {
        // ── Ensino Fundamental — Anos Iniciais (3º ao 5º) ──────────────────────
        $iniciais = ['EF03', 'EF04', 'EF05'];
        $matériasIniciais = ['portugues', 'arte', 'educacao-fisica', 'matematica', 'ciencias', 'historia', 'geografia'];

        foreach ($iniciais as $gradeCode) {
            $gradeName = $grades[$gradeCode]->name;
            foreach ($matériasIniciais as $subjectSlug) {
                $subjectName = $subjects[$subjectSlug]->name;
                Path::updateOrCreate(
                    ['grade_id' => $grades[$gradeCode]->id, 'subject_id' => $subjects[$subjectSlug]->id, 'path_type' => 'regular'],
                    ['title' => "{$gradeName} — {$subjectName}", 'published' => true]
                );
            }
        }

        // ── Ensino Fundamental — Anos Finais (6º ao 9º) ────────────────────────
        // Inclui Inglês como obrigatório a partir do 6º ano
        $finais = ['EF06', 'EF07', 'EF08', 'EF09'];
        $matériasFinais = ['portugues', 'arte', 'ingles', 'matematica', 'ciencias', 'historia', 'geografia'];

        foreach ($finais as $gradeCode) {
            $gradeName = $grades[$gradeCode]->name;
            foreach ($matériasFinais as $subjectSlug) {
                $subjectName = $subjects[$subjectSlug]->name;
                Path::updateOrCreate(
                    ['grade_id' => $grades[$gradeCode]->id, 'subject_id' => $subjects[$subjectSlug]->id, 'path_type' => 'regular'],
                    ['title' => "{$gradeName} — {$subjectName}", 'published' => true]
                );
            }
        }

        // ── Ensino Médio (1º ao 3º) ─────────────────────────────────────────────
        // Linguagens e suas Tecnologias + Matemática + Ciências da Natureza + Ciências Humanas e Sociais
        $medio = ['EM01', 'EM02', 'EM03'];
        $matériasMédio = ['portugues', 'arte', 'ingles', 'matematica', 'biologia', 'fisica', 'quimica', 'historia', 'geografia', 'filosofia', 'sociologia'];

        foreach ($medio as $gradeCode) {
            $gradeName = $grades[$gradeCode]->name;
            foreach ($matériasMédio as $subjectSlug) {
                $subjectName = $subjects[$subjectSlug]->name;
                Path::updateOrCreate(
                    ['grade_id' => $grades[$gradeCode]->id, 'subject_id' => $subjects[$subjectSlug]->id, 'path_type' => 'regular'],
                    ['title' => "{$gradeName} — {$subjectName}", 'published' => true]
                );
            }
        }

    }

    /**
     * @param  array<string, Grade>  $grades
     * @param  array<string, Subject>  $subjects
     */
    private function seedSkillsFromDocument(array $grades, array $subjects): void
    {
        BnccSkill::query()->update([
            'active' => false,
            'version' => 2,
        ]);

        $skills = $this->parseSkillsFromBnccDocument();

        foreach ($skills as $skill) {
            if (! isset($grades[$skill['grade_code']], $subjects[$skill['subject_slug']])) {
                continue;
            }

            BnccSkill::updateOrCreate(
                ['code' => $skill['code']],
                [
                    'grade_id' => $grades[$skill['grade_code']]->id,
                    'subject_id' => $subjects[$skill['subject_slug']]->id,
                    'thematic_unit' => "Bimestre {$skill['bimester']}",
                    'knowledge_object' => $skill['knowledge_object'],
                    'description' => $skill['description'],
                    'version' => 2,
                    'active' => true,
                ]
            );
        }
    }

    /**
     * @return array<int, array{
     *     code: string,
     *     grade_code: string,
     *     subject_slug: string,
     *     bimester: int,
     *     knowledge_object: string,
     *     description: string
     * }>
     */
    private function parseSkillsFromBnccDocument(): array
    {
        $filePath = base_path('docs/bncc.md');

        if (! file_exists($filePath)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($filePath)) ?: [];
        $skills = [];
        $counters = [];

        $currentGradeCode = null;
        $currentSubjectSlug = null;
        $currentBimester = null;

        foreach ($lines as $rawLine) {
            $line = trim((string) $rawLine);

            if ($line === '') {
                continue;
            }

            if (Str::startsWith($line, '📝 NOTAS')) {
                break;
            }

            $cleanLine = trim((string) preg_replace('/^[^\p{L}\d]+/u', '', $line));

            if (preg_match('/^([1-4])º Bimestre$/u', $cleanLine, $bimesterMatch) === 1) {
                $currentBimester = (int) $bimesterMatch[1];

                continue;
            }

            if (preg_match('/^(.+?)\s+—\s+(.+)$/u', $cleanLine, $headingMatch) === 1) {
                $resolvedGradeCode = $this->resolveGradeCodeFromLabel($headingMatch[2]);
                $resolvedSubjectSlug = $this->resolveSubjectSlug($headingMatch[1]);

                if ($resolvedGradeCode && $resolvedSubjectSlug) {
                    $currentGradeCode = $resolvedGradeCode;
                    $currentSubjectSlug = $resolvedSubjectSlug;
                    $currentBimester = null;

                    continue;
                }
            }

            if (! $currentGradeCode || ! $currentSubjectSlug || ! $currentBimester) {
                continue;
            }

            $description = trim($cleanLine, " \t\n\r\0\x0B-•");

            if ($description === '') {
                continue;
            }

            $counterKey = "{$currentGradeCode}|{$currentSubjectSlug}|{$currentBimester}";
            $counters[$counterKey] = ($counters[$counterKey] ?? 0) + 1;
            $position = $counters[$counterKey];

            [$knowledgeObject] = explode(':', $description, 2);
            $knowledgeObject = Str::limit(trim($knowledgeObject), 120, '');

            $skills[] = [
                'code' => $this->buildSkillCode($currentGradeCode, $currentSubjectSlug, $currentBimester, $position),
                'grade_code' => $currentGradeCode,
                'subject_slug' => $currentSubjectSlug,
                'bimester' => $currentBimester,
                'knowledge_object' => $knowledgeObject,
                'description' => $description,
            ];
        }

        return $skills;
    }

    private function resolveGradeCodeFromLabel(string $gradeLabel): ?string
    {
        $normalized = Str::lower($gradeLabel);

        if (preg_match('/([1-9])º ano em/u', $normalized, $match) === 1) {
            return 'EM0'.$match[1];
        }

        if (preg_match('/([3-9])º ano/u', $normalized, $match) === 1) {
            return 'EF0'.$match[1];
        }

        return null;
    }

    private function resolveSubjectSlug(string $subjectLabel): ?string
    {
        $normalized = Str::of($subjectLabel)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9 ]/', '')
            ->squish()
            ->toString();

        $map = [
            'lingua portuguesa' => 'portugues',
            'matematica' => 'matematica',
            'ciencias' => 'ciencias',
            'historia' => 'historia',
            'geografia' => 'geografia',
            'arte' => 'arte',
            'educacao fisica' => 'educacao-fisica',
            'biologia' => 'biologia',
            'fisica' => 'fisica',
            'quimica' => 'quimica',
            'filosofia' => 'filosofia',
            'sociologia' => 'sociologia',
            'lingua inglesa' => 'ingles',
            'ingles' => 'ingles',
        ];

        return $map[$normalized] ?? null;
    }

    private function buildSkillCode(string $gradeCode, string $subjectSlug, int $bimester, int $position): string
    {
        $subjectToken = Str::of($subjectSlug)
            ->replace('-', '')
            ->upper()
            ->substr(0, 6)
            ->toString();

        return sprintf('CUR-%s-%s-B%d-%02d', $gradeCode, $subjectToken, $bimester, $position);
    }
}
