<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // Grades fixes — seeded une seule fois
        // Points utilisés dans le calcul du score de classement
        // firstOrCreate = safe à exécuter plusieurs fois
        $evaluations = [
            ['grade' => 'A+', 'points' => 10, 'description' => 'ممتاز'],
            ['grade' => 'A',  'points' => 8,  'description' => 'جيد جداً'],
            ['grade' => 'B+', 'points' => 6,  'description' => 'جيد'],
            ['grade' => 'B',  'points' => 4,  'description' => 'مقبول'],
            ['grade' => 'C',  'points' => 2,  'description' => 'ضعيف'],
            ['grade' => 'D',  'points' => 0,  'description' => 'غائب / لم يُقيَّم'],
        ];

        foreach ($evaluations as $eval) {
            Evaluation::firstOrCreate(
                ['grade' => $eval['grade']],
                $eval
            );
        }

        $this->command->info('✅ EvaluationSeeder: 6 grades créés');
    }
}