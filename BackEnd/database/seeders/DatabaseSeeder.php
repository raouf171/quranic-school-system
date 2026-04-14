<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ORDRE IMPORTANT:
        // 1. Evaluations en premier (table de référence)
        // 2. Admin (pas de dépendances)
        // 3. TestData en dernier (dépend de tout)
        $this->call([
            EvaluationSeeder::class,
            AdminSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}