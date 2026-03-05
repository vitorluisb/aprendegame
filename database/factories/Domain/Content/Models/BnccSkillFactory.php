<?php

namespace Database\Factories\Domain\Content\Models;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BnccSkill> */
class BnccSkillFactory extends Factory
{
    protected $model = BnccSkill::class;

    public function definition(): array
    {
        $grade = Grade::inRandomOrder()->first() ?? Grade::factory()->create();
        $subject = Subject::inRandomOrder()->first() ?? Subject::factory()->create();

        $gradeCode = strtoupper(str_replace(['º Ano EF', 'º Ano EM', ' '], ['', '', ''], $grade->name));
        $subjectCode = strtoupper(substr(str_replace(['á','é','ê','í','ó','ô','ú','ã','ç'], ['a','e','e','i','o','o','u','a','c'], $subject->name), 0, 2));

        return [
            'code' => 'EF'.fake()->unique()->numerify('##').strtoupper(fake()->lexify('??')),
            'description' => fake()->sentence(12),
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'thematic_unit' => fake()->words(3, true),
            'knowledge_object' => fake()->words(4, true),
            'version' => 1,
            'active' => true,
        ];
    }
}
