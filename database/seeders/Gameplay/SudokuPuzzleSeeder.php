<?php

namespace Database\Seeders\Gameplay;

use App\Domain\Sudoku\Models\SudokuPuzzle;
use Illuminate\Database\Seeder;

class SudokuPuzzleSeeder extends Seeder
{
    public function run(): void
    {
        $puzzles = [
            [
                'difficulty' => 'easy',
                'puzzle_string' => '534070900672095300190340500850760400406850700710924000960530200280419000345086100',
                'solution_string' => '534678912672195348198342567859761423426853791713924856961537284287419635345286179',
                'clues_count' => 47,
                'is_active' => true,
            ],
            [
                'difficulty' => 'easy',
                'puzzle_string' => '407369025630158007908704306025407109701506402306902708209603501503201604104805203',
                'solution_string' => '417369825632158947958724316825437169791586432346912758289643571573291684164875293',
                'clues_count' => 55,
                'is_active' => true,
            ],
            [
                'difficulty' => 'easy',
                'puzzle_string' => '190743062401805907870192040380459010610387090540216030760524080920671050250938070',
                'solution_string' => '195743862431865927876192543387459216612387495549216738763524189928671354254938671',
                'clues_count' => 55,
                'is_active' => true,
            ],
            [
                'difficulty' => 'medium',
                'puzzle_string' => '530070900602005300100300500800700400400850700700904000900500200200409000305080100',
                'solution_string' => '534678912672195348198342567859761423426853791713924856961537284287419635345286179',
                'clues_count' => 31,
                'is_active' => true,
            ],
            [
                'difficulty' => 'medium',
                'puzzle_string' => '400309005030050007900704006020400109700080002306002008200603001070200600100805003',
                'solution_string' => '417369825632158947958724316825437169791586432346912758289643571573291684164875293',
                'clues_count' => 33,
                'is_active' => true,
            ],
            [
                'difficulty' => 'medium',
                'puzzle_string' => '090703060400805007070100503300409006010080090500206008060504009900601004050908070',
                'solution_string' => '195743862431865927876192543387459216612387495549216738763524189928671354254938671',
                'clues_count' => 35,
                'is_active' => true,
            ],
            [
                'difficulty' => 'hard',
                'puzzle_string' => '500070002070005000100040007050001000400050001010004000900030004080009000300080000',
                'solution_string' => '534678912672195348198342567859761423426853791713924856961537284287419635345286179',
                'clues_count' => 22,
                'is_active' => true,
            ],
            [
                'difficulty' => 'hard',
                'puzzle_string' => '007000800600050007008000300800030009001000400300010008009000500500090004004000200',
                'solution_string' => '417369825632158947958724316825437169791586432346912758289643571573291684164875293',
                'clues_count' => 22,
                'is_active' => true,
            ],
            [
                'difficulty' => 'hard',
                'puzzle_string' => '100003002030005020800090500080400010600080005040200030700020100020001050000000000',
                'solution_string' => '195743862431865927876192543387459216612387495549216738763524189928671354254938671',
                'clues_count' => 24,
                'is_active' => true,
            ],
        ];

        foreach ($puzzles as $puzzle) {
            SudokuPuzzle::query()->updateOrCreate(
                [
                    'difficulty' => $puzzle['difficulty'],
                    'puzzle_string' => $puzzle['puzzle_string'],
                ],
                [
                    'solution_string' => $puzzle['solution_string'],
                    'clues_count' => $puzzle['clues_count'],
                    'is_active' => $puzzle['is_active'],
                ]
            );
        }
    }
}
