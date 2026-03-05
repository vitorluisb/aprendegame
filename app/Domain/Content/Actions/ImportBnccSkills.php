<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;

class ImportBnccSkills
{
    /** @return array{imported: int, errors: string[]} */
    public function handle(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return ['imported' => 0, 'errors' => ["Não foi possível abrir o arquivo: {$filePath}"]];
        }

        $headers = fgetcsv($handle);
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);

            try {
                $gradeId = Grade::where('code', $data['grade'])->value('id');
                $subjectId = Subject::where('slug', $data['subject'])->value('id');

                if (!$gradeId) {
                    $errors[] = "Linha {$data['code']}: grade '{$data['grade']}' não encontrada.";
                    continue;
                }

                if (!$subjectId) {
                    $errors[] = "Linha {$data['code']}: subject '{$data['subject']}' não encontrado.";
                    continue;
                }

                BnccSkill::updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'description' => $data['description'],
                        'grade_id' => $gradeId,
                        'subject_id' => $subjectId,
                        'thematic_unit' => $data['thematic_unit'] ?? null,
                        'knowledge_object' => $data['knowledge_object'] ?? null,
                    ]
                );

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Linha {$data['code']}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        return ['imported' => $imported, 'errors' => $errors];
    }
}
