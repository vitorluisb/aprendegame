<?php

namespace Database\Seeders;

use Database\Seeders\Gameplay\StudentNeuronsSeeder;
use Database\Seeders\Gameplay\TrailContentSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BnccSeeder::class,
            ShopItemSeeder::class,
            BadgeSeeder::class,
            SchoolSeeder::class,
            UserSeeder::class,
            SchoolMemberSeeder::class,
            StudentSeeder::class,
            StudentGuardianSeeder::class,
            TrailContentSeeder::class,
            StudentNeuronsSeeder::class,
        ]);
    }
}
