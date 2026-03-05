<?php

namespace Database\Seeders;

use App\Domain\Gameplay\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // Sequência (streak)
            [
                'name' => 'Primeira Faísca',
                'description' => 'Complete sua primeira aula.',
                'icon' => 'zap',
                'condition_type' => 'lessons_completed',
                'condition_value' => 1,
                'active' => true,
            ],
            [
                'name' => 'Semana Perfeita',
                'description' => 'Mantenha uma sequência de 7 dias.',
                'icon' => 'flame',
                'condition_type' => 'streak_days',
                'condition_value' => 7,
                'active' => true,
            ],
            [
                'name' => 'Mês Dedicado',
                'description' => 'Mantenha uma sequência de 30 dias.',
                'icon' => 'calendar-check',
                'condition_type' => 'streak_days',
                'condition_value' => 30,
                'active' => true,
            ],
            [
                'name' => 'Cem Dias de Glória',
                'description' => 'Mantenha uma sequência de 100 dias.',
                'icon' => 'trophy',
                'condition_type' => 'streak_days',
                'condition_value' => 100,
                'active' => true,
            ],
            // XP
            [
                'name' => 'Primeiros Passos',
                'description' => 'Acumule 100 XP.',
                'icon' => 'footprints',
                'condition_type' => 'total_xp',
                'condition_value' => 100,
                'active' => true,
            ],
            [
                'name' => 'Estudante Dedicado',
                'description' => 'Acumule 1.000 XP.',
                'icon' => 'book',
                'condition_type' => 'total_xp',
                'condition_value' => 1000,
                'active' => true,
            ],
            [
                'name' => 'Mestre do Saber',
                'description' => 'Acumule 10.000 XP.',
                'icon' => 'graduation-cap',
                'condition_type' => 'total_xp',
                'condition_value' => 10000,
                'active' => true,
            ],
            [
                'name' => 'Lenda Acadêmica',
                'description' => 'Acumule 50.000 XP.',
                'icon' => 'crown',
                'condition_type' => 'total_xp',
                'condition_value' => 50000,
                'active' => true,
            ],
            // Lições concluídas
            [
                'name' => 'Explorador',
                'description' => 'Complete 10 aulas.',
                'icon' => 'compass',
                'condition_type' => 'lessons_completed',
                'condition_value' => 10,
                'active' => true,
            ],
            [
                'name' => 'Estudioso',
                'description' => 'Complete 50 aulas.',
                'icon' => 'books',
                'condition_type' => 'lessons_completed',
                'condition_value' => 50,
                'active' => true,
            ],
            [
                'name' => 'Maratonista do Conhecimento',
                'description' => 'Complete 200 aulas.',
                'icon' => 'medal',
                'condition_type' => 'lessons_completed',
                'condition_value' => 200,
                'active' => true,
            ],
            // Precisão
            [
                'name' => 'Olho de Águia',
                'description' => 'Acerte 10 questões seguidas sem errar.',
                'icon' => 'target',
                'condition_type' => 'correct_streak',
                'condition_value' => 10,
                'active' => true,
            ],
            [
                'name' => 'Perfeição',
                'description' => 'Complete uma aula com 100% de acertos.',
                'icon' => 'star',
                'condition_type' => 'perfect_lesson',
                'condition_value' => 1,
                'active' => true,
            ],
            // Domínio
            [
                'name' => 'Habilidoso',
                'description' => 'Domine 5 habilidades BNCC.',
                'icon' => 'award',
                'condition_type' => 'skills_mastered',
                'condition_value' => 5,
                'active' => true,
            ],
            [
                'name' => 'Mestre das Habilidades',
                'description' => 'Domine 20 habilidades BNCC.',
                'icon' => 'shield-check',
                'condition_type' => 'skills_mastered',
                'condition_value' => 20,
                'active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}
