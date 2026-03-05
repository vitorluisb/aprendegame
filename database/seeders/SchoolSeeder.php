<?php

namespace Database\Seeders;

use App\Domain\Accounts\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        School::factory()->count(3)->create();
    }
}
