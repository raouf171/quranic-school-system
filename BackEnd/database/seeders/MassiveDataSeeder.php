<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\DateEntry;
use App\Models\Evaluation;
use App\Models\Halaqa;
use App\Models\HalaqaSchedule;
use App\Models\Memorization;
use App\Models\ParentProfile;
use App\Models\Payment;
use App\Models\Ranking;
use App\Models\Revision;
use App\Models\Seance;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MassiveDataSeeder extends Seeder
{
    private array $surahMaxVerses = [
        1=>7,2=>286,3=>200,4=>176,5=>120,6=>165,7=>206,8=>75,9=>129,10=>109,
        11=>123,12=>111,13=>43,14=>52,15=>99,16=>128,17=>111,18=>110,19=>98,20=>135,
        21=>112,22=>78,23=>118,24=>64,25=>77,26=>227,27=>93,28=>88,29=>69,30=>60,
        31=>34,32=>30,33=>73,34=>54,35=>45,36=>83,37=>182,38=>88,39=>75,40=>85,
        41=>54,42=>53,43=>89,44=>59,45=>37,46=>35,47=>38,48=>29,49=>18,50=>45,
        51=>60,52=>49,53=>62,54=>55,55=>78,56=>96,57=>29,58=>22,59=>24,60=>13,
        61=>14,62=>11,63=>11,64=>18,65=>12,66=>12,67=>30,68=>52,69=>52,70=>44,
        71=>28,72=>28,73=>20,74=>56,75=>40,76=>31,77=>50,78=>40,79=>46,80=>42,
        81=>29,82=>19,83=>36,84=>25,85=>22,86=>17,87=>19,88=>26,89=>30,90=>20,
        91=>15,92=>21,93=>11,94=>8,95=>8,96=>19,97=>5,98=>8,99=>8,100=>11,
        101=>11,102=>8,103=>3,104=>9,105=>5,106=>4,107=>7,108=>3,109=>6,110=>3,
        111=>5,112=>4,113=>5,114=>6,
    ];

    private array $evalData = [];
    private array $evalWeighted = [];

    public function run(): void
    {
        $this->loadEvaluations();

        foreach ([Attendance::class, Memorization::class, Revision::class, Payment::class] as $m) {
            $m::unsetEventDispatcher();
        }

        $this->command->info('⏳ Creating 6 classrooms...');
        $classrooms = $this->createClassrooms();

        $this->command->info('⏳ Creating 10 teacher accounts...');
        $teachers = $this->createTeachers();

        $this->command->info('⏳ Creating 12 halaqat with schedules...');
        [$halaqat, $schedulesByHalaqa] = $this->createHalaqat($teachers, $classrooms);

        $this->command->info('⏳ Creating 20 parent accounts...');
        $parents = $this->createParents();

        $this->command->info('⏳ Creating 100 students...');
        $studentsByHalaqa = $this->createStudents($parents, $halaqat);

        $this->command->info('⏳ Generating seances (10+ weeks of sessions)...');
        $seancesByHalaqa = $this->createSeances($halaqat, $schedulesByHalaqa);

        $this->command->info('⏳ Recording attendance...');
        $presentMap = $this->createAttendance($seancesByHalaqa, $studentsByHalaqa);

        $this->command->info('⏳ Recording memorization (hifz)...');
        $this->createMemorizations($seancesByHalaqa, $presentMap, $halaqat);

        $this->command->info('⏳ Recording revisions (muraja\'ah)...');
        $this->createRevisions($seancesByHalaqa, $presentMap, $halaqat);

        $this->command->info('⏳ Creating payment records (6 months)...');
        $this->createPayments($studentsByHalaqa);

        $this->command->info('⏳ Creating 15 announcements...');
        $this->createAnnouncements();

        $this->command->info('⏳ Calculating rankings (3 months)...');
        $this->calculateRankings($halaqat, $studentsByHalaqa);

        $this->command->info('');
        $this->command->info('✅ MassiveDataSeeder complete!');
        $this->command->info('══════════════════════════════════════════');
        $this->command->info('  Admin:     admin@school.com          / Admin@1234');
        $this->command->info('  Teachers:  teacher1..10@school.com   / Teacher@1234');
        $this->command->info('  Parents:   parent1..20@school.com    / Parent@1234');
        $this->command->info('══════════════════════════════════════════');
    }

    /* ───────────────── helpers ───────────────── */

    private function loadEvaluations(): void
    {
        $evals = Evaluation::all();
        if ($evals->isEmpty()) {
            $this->command->error('❌ No evaluations found! Run EvaluationSeeder first.');
            return;
        }
        foreach ($evals as $e) {
            $this->evalData[$e->id] = ['grade' => $e->grade, 'points' => $e->points];
        }
        $weights = ['A+' => 3, 'A' => 5, 'B+' => 5, 'B' => 4, 'C' => 2, 'D' => 1];
        foreach ($evals as $e) {
            $w = $weights[$e->grade] ?? 1;
            for ($i = 0; $i < $w; $i++) {
                $this->evalWeighted[] = $e->id;
            }
        }
    }

    private function pickEval(): array
    {
        $id = $this->evalWeighted[array_rand($this->evalWeighted)];
        return ['id' => $id, ...$this->evalData[$id]];
    }

    private function getSurahRange(int $halaqaIdx): array
    {
        return match (true) {
            in_array($halaqaIdx, [0, 3, 6, 9])  => [100, 114],
            in_array($halaqaIdx, [1, 4, 7, 10]) => [67, 99],
            default                               => [1, 66],
        };
    }

    /* ───────────────── classrooms ───────────────── */

    private function createClassrooms(): array
    {
        $rows = [
            ['Salle Al-Fatiha', 'Bâtiment Principal', 30],
            ['Salle Al-Baqara', 'Bâtiment Principal', 25],
            ['Salle Ar-Rahman', 'Bâtiment Principal', 25],
            ['Salle Al-Mulk',   'Annexe',             20],
            ['Salle Yasin',     'Annexe',             20],
            ['Salle Al-Kahf',   'Annexe',             15],
        ];

        return array_map(fn ($r) => Classroom::firstOrCreate(
            ['name' => $r[0]],
            ['building' => $r[1], 'capacity' => $r[2], 'is_available' => true]
        ), $rows);
    }

    /* ───────────────── teachers ───────────────── */

    private function createTeachers(): array
    {
        $rows = [
            ['Omar Benali',       'teacher1@school.com',  '2021-09-01'],
            ['Youssef Hamdi',     'teacher2@school.com',  '2022-01-15'],
            ['Abdellah Bouzid',   'teacher3@school.com',  '2020-09-01'],
            ['Mohamed Khelifi',   'teacher4@school.com',  '2023-02-01'],
            ['Ibrahim Saadi',     'teacher5@school.com',  '2019-09-01'],
            ['Rachid Mebarki',    'teacher6@school.com',  '2022-09-01'],
            ['Noureddine Ferhat', 'teacher7@school.com',  '2021-01-15'],
            ['Khaled Djebbar',    'teacher8@school.com',  '2023-09-01'],
            ['Sofiane Amrani',    'teacher9@school.com',  '2024-01-15'],
            ['Bilal Toumi',       'teacher10@school.com', '2024-09-01'],
        ];

        $teachers = [];
        foreach ($rows as $r) {
            $acct = Account::firstOrCreate(
                ['email' => $r[1]],
                ['password' => 'Teacher@1234', 'role' => 'teacher', 'is_active' => true]
            );
            $teachers[] = Teacher::firstOrCreate(
                ['account_id' => $acct->id],
                ['name' => $r[0], 'hiring_date' => $r[2], 'is_available' => true]
            );
        }
        return $teachers;
    }

    /* ───────────────── halaqat + schedules ───────────────── */

    private function createHalaqat(array $teachers, array $classrooms): array
    {
        // [name, gender, max_students, teacher_index, schedules: [[weekday, start, end, classroom_idx]]]
        $cfg = [
            ['حلقة النور',    'male',   12, 0, [[0,'08:00','09:30',0],[2,'08:00','09:30',0],[4,'08:00','09:30',0]]],
            ['حلقة الإيمان',  'male',   10, 1, [[0,'10:00','11:30',0],[2,'10:00','11:30',0],[4,'10:00','11:30',0]]],
            ['حلقة البركة',   'male',    8, 2, [[1,'08:00','09:30',1],[3,'08:00','09:30',1]]],
            ['حلقة التقوى',   'male',   12, 3, [[1,'10:00','11:30',1],[3,'10:00','11:30',1]]],
            ['حلقة الفرقان',  'male',   10, 4, [[0,'14:00','15:30',2],[4,'14:00','15:30',2]]],
            ['حلقة الإحسان',  'male',    8, 5, [[2,'14:00','15:30',2],[4,'16:00','17:30',2]]],
            ['حلقة الرحمة',   'female', 12, 6, [[0,'08:00','09:30',3],[2,'08:00','09:30',3],[4,'08:00','09:30',3]]],
            ['حلقة الهدى',    'female', 10, 7, [[0,'10:00','11:30',3],[2,'10:00','11:30',3],[4,'10:00','11:30',3]]],
            ['حلقة الفجر',    'female',  8, 0, [[1,'08:00','09:30',4],[3,'08:00','09:30',4]]],
            ['حلقة الصبر',    'female', 12, 1, [[1,'10:00','11:30',4],[3,'10:00','11:30',4]]],
            ['حلقة النجاح',   'female', 10, 8, [[0,'14:00','15:30',5],[4,'14:00','15:30',5]]],
            ['حلقة الجنة',    'female',  8, 9, [[2,'16:00','17:30',5],[4,'16:00','17:30',5]]],
        ];

        $halaqat = [];
        $schedulesByHalaqa = [];

        foreach ($cfg as $c) {
            $h = Halaqa::updateOrCreate(
                ['name' => $c[0]],
                ['teacher_id' => $teachers[$c[3]]->id, 'gender' => $c[1], 'max_students' => $c[2], 'is_active' => true]
            );
            $halaqat[] = $h;

            $scheds = [];
            foreach ($c[4] as $pos => $s) {
                $scheds[] = HalaqaSchedule::firstOrCreate(
                    ['halaqa_id' => $h->id, 'weekday' => $s[0], 'start_time' => $s[1], 'end_time' => $s[2], 'classroom_id' => $classrooms[$s[3]]->id],
                    ['is_active' => true, 'position' => $pos]
                );
            }
            $schedulesByHalaqa[$h->id] = $scheds;
        }

        return [$halaqat, $schedulesByHalaqa];
    }

    /* ───────────────── parents ───────────────── */

    private function createParents(): array
    {
        // [name, email, phone, occupation, address, maleCount, femaleCount]
        $rows = [
            ['Mohamed Mansouri',   'parent1@school.com',  '+213555000001', 'Ingénieur',    'Alger',       3, 3],
            ['Karim Boudiaf',      'parent2@school.com',  '+213555000002', 'Médecin',      'Oran',        3, 3],
            ['Ali Cherif',         'parent3@school.com',  '+213555000003', 'Enseignant',   'Constantine', 3, 3],
            ['Hamid Benarbia',     'parent4@school.com',  '+213555000004', 'Commerçant',   'Blida',       3, 3],
            ['Samir Benmoussa',    'parent5@school.com',  '+213555000005', 'Pharmacien',   'Sétif',       3, 2],
            ['Rachid Lahouel',     'parent6@school.com',  '+213555000006', 'Avocat',       'Annaba',      2, 3],
            ['Mustapha Ghrib',     'parent7@school.com',  '+213555000007', 'Architecte',   'Tlemcen',     3, 2],
            ['Nabil Djaballah',    'parent8@school.com',  '+213555000008', 'Comptable',    'Béjaïa',      2, 3],
            ['Fares Medjoubi',     'parent9@school.com',  '+213555000009', 'Fonctionnaire','Mostaganem',  3, 2],
            ['Djamel Boucherit',   'parent10@school.com', '+213555000010', 'Professeur',   'Tizi Ouzou',  2, 3],
            ['Hocine Zerhouni',    'parent11@school.com', '+213555000011', 'Journaliste',  'Djelfa',      3, 2],
            ['Tarik Belkacemi',    'parent12@school.com', '+213555000012', 'Informaticien','Médéa',       2, 3],
            ['Lotfi Mekideche',    'parent13@school.com', '+213555000013', 'Mécanicien',   'Biskra',      2, 2],
            ['Farid Bouazza',      'parent14@school.com', '+213555000014', 'Électricien',  'Chlef',       2, 2],
            ['Nadir Sellami',      'parent15@school.com', '+213555000015', 'Plombier',     'Ghardaïa',    2, 2],
            ['Redouane Hamdani',   'parent16@school.com', '+213555000016', 'Agriculteur',  'Tiaret',      2, 2],
            ['Abdelkader Ouali',   'parent17@school.com', '+213555000017', 'Menuisier',    'Jijel',       2, 3],
            ['Zoubir Benattia',    'parent18@school.com', '+213555000018', 'Chauffeur',    'Saïda',       3, 2],
            ['Mourad Rahmouni',    'parent19@school.com', '+213555000019', 'Peintre',      'M\'sila',     2, 3],
            ['Said Benaissa',      'parent20@school.com', '+213555000020', 'Artisan',      'Batna',       3, 2],
        ];

        $parents = [];
        foreach ($rows as $r) {
            $acct = Account::firstOrCreate(
                ['email' => $r[1]],
                ['password' => 'Parent@1234', 'role' => 'parent', 'is_active' => true]
            );
            $prof = ParentProfile::updateOrCreate(
                ['account_id' => $acct->id],
                ['name' => $r[0], 'phone' => $r[2], 'occupation' => $r[3], 'address' => $r[4]]
            );
            $parents[] = [
                'profile'    => $prof,
                'males'      => $r[5],
                'females'    => $r[6],
                'familyName' => explode(' ', $r[0], 2)[1] ?? $r[0],
            ];
        }
        return $parents;
    }

    /* ───────────────── students ───────────────── */

    private function createStudents(array $parents, array $halaqat): array
    {
        $maleNames = [
            'Ahmed','Mohamed','Youssef','Ibrahim','Adam','Ayoub','Amine','Rayan',
            'Ilyes','Zakaria','Ismail','Anis','Hamza','Bilal','Walid','Fares',
            'Sami','Nabil','Rami','Mourad','Mehdi','Kamel','Nassim','Oussama',
            'Tarek','Djamel','Adel','Abderrahmane','Louay','Sohaib','Anas','Omar',
            'Yacine','Hakim','Saad','Hichem','Zaki','Nadir','Fethi','Lamine',
            'Sofiane','Idriss','Djaber','Yazid','Houssem','Brahim','Salim','Azzedine',
            'Lotfi','Farouk',
        ];

        $femaleNames = [
            'Fatima','Aicha','Meriem','Sara','Khadija','Amina','Nour','Djamila',
            'Leila','Samira','Yasmine','Imane','Sana','Rania','Hana','Asma',
            'Dalia','Ines','Lina','Malak','Rym','Chaima','Wafa','Manel',
            'Ikram','Nawel','Karima','Soumia','Nawal','Houda','Amel','Zineb',
            'Ghania','Nadia','Farida','Lamia','Salima','Noura','Hadjer','Baya',
            'Siham','Djihane','Selma','Farah','Nesrine','Sabrina','Dounia','Rima',
            'Rahma','Hanane',
        ];

        $schoolLevels  = ['kindergarten','primary','primary','primary','middle_cem','middle_cem','high_school','university'];
        $socialStates  = array_merge(array_fill(0, 15, 'normal'), ['father_deceased','mother_deceased','divorced_parents']);
        $relationships = ['father','father','father','father','father','mother','uncle','grandfather','legal_guardian'];

        $maleHalaqat  = array_values(array_filter($halaqat, fn ($h) => $h->gender === 'male'));
        $femaleHalaqat = array_values(array_filter($halaqat, fn ($h) => $h->gender === 'female'));

        $mIdx = 0;
        $fIdx = 0;
        $mName = 0;
        $fName = 0;
        $studentsByHalaqa = [];

        foreach ($parents as $p) {
            $parent = $p['profile'];
            $family = $p['familyName'];

            for ($i = 0; $i < $p['males']; $i++) {
                $halaqa      = $maleHalaqat[$mIdx % count($maleHalaqat)];
                $socialState = $socialStates[array_rand($socialStates)];
                $feeStatus   = in_array($socialState, ['father_deceased', 'mother_deceased']) ? 'exempt' : 'pending';

                $s = Student::updateOrCreate(
                    ['full_name' => $maleNames[$mName] . ' ' . $family, 'parent_id' => $parent->id],
                    [
                        'halaqa_id'           => $halaqa->id,
                        'gender'              => 'male',
                        'relationship_nature' => $relationships[array_rand($relationships)],
                        'school_level'        => $schoolLevels[array_rand($schoolLevels)],
                        'birth_date'          => sprintf('%04d-%02d-%02d', rand(2008, 2020), rand(1, 12), rand(1, 28)),
                        'social_state'        => $socialState,
                        'fee_status'          => $feeStatus,
                    ]
                );
                $studentsByHalaqa[$halaqa->id][] = $s->id;
                $mIdx++;
                $mName++;
            }

            for ($i = 0; $i < $p['females']; $i++) {
                $halaqa      = $femaleHalaqat[$fIdx % count($femaleHalaqat)];
                $socialState = $socialStates[array_rand($socialStates)];
                $feeStatus   = in_array($socialState, ['father_deceased', 'mother_deceased']) ? 'exempt' : 'pending';

                $s = Student::updateOrCreate(
                    ['full_name' => $femaleNames[$fName] . ' ' . $family, 'parent_id' => $parent->id],
                    [
                        'halaqa_id'           => $halaqa->id,
                        'gender'              => 'female',
                        'relationship_nature' => $relationships[array_rand($relationships)],
                        'school_level'        => $schoolLevels[array_rand($schoolLevels)],
                        'birth_date'          => sprintf('%04d-%02d-%02d', rand(2008, 2020), rand(1, 12), rand(1, 28)),
                        'social_state'        => $socialState,
                        'fee_status'          => $feeStatus,
                    ]
                );
                $studentsByHalaqa[$halaqa->id][] = $s->id;
                $fIdx++;
                $fName++;
            }
        }

        $total = array_sum(array_map('count', $studentsByHalaqa));
        $this->command->info("   → {$total} students across " . count($studentsByHalaqa) . " halaqat");
        return $studentsByHalaqa;
    }

    /* ───────────────── seances ───────────────── */

    private function createSeances(array $halaqat, array $schedulesByHalaqa): array
    {
        $startDate = Carbon::create(2026, 3, 1);
        $endDate   = Carbon::create(2026, 5, 17);
        $today     = Carbon::today();

        $cancelReasons = [
            'عطلة رسمية',
            'غياب الأستاذ لظروف صحية',
            'أعمال صيانة في القاعة',
            'ظروف جوية سيئة',
            'اجتماع إداري طارئ',
        ];

        $seancesByHalaqa = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dow = $current->dayOfWeek; // 0=Sun

            foreach ($halaqat as $halaqa) {
                if (! isset($schedulesByHalaqa[$halaqa->id])) {
                    continue;
                }

                foreach ($schedulesByHalaqa[$halaqa->id] as $sched) {
                    if ($sched->weekday !== $dow) {
                        continue;
                    }

                    $dateEntry = DateEntry::firstOrCreate(
                        ['date_value' => $current->toDateString(), 'created_by' => $halaqa->teacher_id]
                    );

                    $isPast = $current->lt($today);
                    $status = $isPast
                        ? (rand(1, 100) <= 92 ? 'held' : 'cancelled')
                        : 'scheduled';

                    $seance = Seance::updateOrCreate(
                        ['halaqa_id' => $halaqa->id, 'schedule_id' => $sched->id, 'occurrence_date' => $current->toDateString()],
                        [
                            'created_by'    => $halaqa->teacher_id,
                            'classroom_id'  => $sched->classroom_id,
                            'date_id'       => $dateEntry->id,
                            'start_time'    => $sched->start_time,
                            'end_time'      => $sched->end_time,
                            'status'        => $status,
                            'cancel_reason' => $status === 'cancelled' ? $cancelReasons[array_rand($cancelReasons)] : null,
                        ]
                    );

                    $seancesByHalaqa[$halaqa->id][] = $seance;
                }
            }
            $current->addDay();
        }

        $total = array_sum(array_map('count', $seancesByHalaqa));
        $this->command->info("   → {$total} seances generated");
        return $seancesByHalaqa;
    }

    /* ───────────────── attendance ───────────────── */

    private function createAttendance(array $seancesByHalaqa, array $studentsByHalaqa): array
    {
        $statusPool = ['present','present','present','present','present','present',
                       'present','present','absent','late','late','excused'];
        $now        = now();
        $presentMap = [];
        $records    = [];

        foreach ($seancesByHalaqa as $halaqaId => $seances) {
            $studentIds = $studentsByHalaqa[$halaqaId] ?? [];
            if (empty($studentIds)) {
                continue;
            }

            foreach ($seances as $seance) {
                if ($seance->status !== 'held') {
                    continue;
                }

                $presentMap[$seance->id] = [];

                foreach ($studentIds as $sid) {
                    $status = $statusPool[array_rand($statusPool)];
                    if (in_array($status, ['present', 'late'])) {
                        $presentMap[$seance->id][] = $sid;
                    }

                    $records[] = [
                        'seance_id'        => $seance->id,
                        'student_id'       => $sid,
                        'status'           => $status,
                        'evaluation_grade' => null,
                        'points'           => $status === 'present' ? 1 : 0,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }

        $this->command->info("   → " . count($records) . " attendance records");
        return $presentMap;
    }

    /* ───────────────── memorizations ───────────────── */

    private function createMemorizations(array $seancesByHalaqa, array $presentMap, array $halaqat): void
    {
        $halaqaIdx = [];
        foreach ($halaqat as $i => $h) {
            $halaqaIdx[$h->id] = $i;
        }

        $now     = now();
        $records = [];

        foreach ($seancesByHalaqa as $halaqaId => $seances) {
            $idx = $halaqaIdx[$halaqaId] ?? 0;
            [$surahMin, $surahMax] = $this->getSurahRange($idx);
            $teacherId = $halaqat[$idx]->teacher_id;

            foreach ($seances as $seance) {
                $present = $presentMap[$seance->id] ?? [];
                if (empty($present)) {
                    continue;
                }

                shuffle($present);
                $pick = max(1, (int) (count($present) * 0.65));

                for ($i = 0; $i < $pick; $i++) {
                    $eval  = $this->pickEval();
                    $surah = rand($surahMin, $surahMax);
                    $maxV  = $this->surahMaxVerses[$surah] ?? 10;
                    $vs    = rand(1, max(1, $maxV - 5));
                    $ve    = min($maxV, $vs + rand(3, 10));

                    $records[] = [
                        'seance_id'        => $seance->id,
                        'student_id'       => $present[$i],
                        'teacher_id'       => $teacherId,
                        'evaluation_id'    => $eval['id'],
                        'surah_start'      => $surah,
                        'verse_start'      => $vs,
                        'surah_end'        => $surah,
                        'verse_end'        => $ve,
                        'evaluation_grade' => $eval['grade'],
                        'points'           => $eval['points'],
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('memorizations')->insert($chunk);
        }

        $this->command->info("   → " . count($records) . " memorization records");
    }

    /* ───────────────── revisions ───────────────── */

    private function createRevisions(array $seancesByHalaqa, array $presentMap, array $halaqat): void
    {
        $halaqaIdx = [];
        foreach ($halaqat as $i => $h) {
            $halaqaIdx[$h->id] = $i;
        }

        $now     = now();
        $records = [];

        foreach ($seancesByHalaqa as $halaqaId => $seances) {
            $idx = $halaqaIdx[$halaqaId] ?? 0;
            [$surahMin, $surahMax] = $this->getSurahRange($idx);
            $surahMin  = max(1, $surahMin - 10);
            $teacherId = $halaqat[$idx]->teacher_id;

            foreach ($seances as $seance) {
                $present = $presentMap[$seance->id] ?? [];
                if (empty($present)) {
                    continue;
                }

                shuffle($present);
                $pick = max(1, (int) (count($present) * 0.35));

                for ($i = 0; $i < $pick; $i++) {
                    $eval  = $this->pickEval();
                    $surah = rand($surahMin, $surahMax);
                    $maxV  = $this->surahMaxVerses[$surah] ?? 10;
                    $vs    = rand(1, max(1, $maxV - 5));
                    $ve    = min($maxV, $vs + rand(5, 15));

                    $records[] = [
                        'seance_id'        => $seance->id,
                        'student_id'       => $present[$i],
                        'teacher_id'       => $teacherId,
                        'evaluation_id'    => $eval['id'],
                        'surah_start'      => $surah,
                        'verse_start'      => $vs,
                        'surah_end'        => $surah,
                        'verse_end'        => $ve,
                        'evaluation_grade' => $eval['grade'],
                        'points'           => $eval['points'],
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('revisions')->insert($chunk);
        }

        $this->command->info("   → " . count($records) . " revision records");
    }

    /* ───────────────── payments ───────────────── */

    private function createPayments(array $studentsByHalaqa): void
    {
        $allStudentIds = array_unique(array_merge(...array_values($studentsByHalaqa)));

        $exemptIds = Student::whereIn('id', $allStudentIds)
            ->where('fee_status', 'exempt')
            ->pluck('id')
            ->flip()
            ->toArray();

        $now     = now();
        $records = [];
        $months  = ['2025-12', '2026-01', '2026-02', '2026-03', '2026-04', '2026-05'];

        foreach ($allStudentIds as $sid) {
            $exempt = isset($exemptIds[$sid]);

            foreach ($months as $mi => $month) {
                $dueDate = Carbon::createFromFormat('Y-m', $month)->day(5)->toDateString();

                if ($exempt) {
                    $status   = 'exempt';
                    $paidDate = null;
                } elseif ($mi <= 2) {
                    // Dec–Feb: almost all paid
                    $paid     = rand(1, 100) <= 92;
                    $status   = $paid ? 'paid' : 'late';
                    $paidDate = Carbon::createFromFormat('Y-m', $month)->day($paid ? rand(1, 8) : rand(15, 28))->toDateString();
                } elseif ($mi === 3) {
                    // Mar: mostly paid
                    $r = rand(1, 100);
                    if ($r <= 82) {
                        $status = 'paid';
                        $paidDate = Carbon::create(2026, 3, rand(1, 10))->toDateString();
                    } elseif ($r <= 94) {
                        $status = 'late';
                        $paidDate = Carbon::create(2026, 3, rand(18, 28))->toDateString();
                    } else {
                        $status = 'pending';
                        $paidDate = null;
                    }
                } elseif ($mi === 4) {
                    // Apr: mixed
                    $r = rand(1, 100);
                    if ($r <= 68) {
                        $status = 'paid';
                        $paidDate = Carbon::create(2026, 4, rand(1, 12))->toDateString();
                    } elseif ($r <= 88) {
                        $status = 'pending';
                        $paidDate = null;
                    } else {
                        $status = 'late';
                        $paidDate = null;
                    }
                } else {
                    // May: many pending (current month)
                    $r = rand(1, 100);
                    if ($r <= 38) {
                        $status = 'paid';
                        $paidDate = Carbon::create(2026, 5, rand(1, 10))->toDateString();
                    } elseif ($r <= 82) {
                        $status = 'pending';
                        $paidDate = null;
                    } else {
                        $status = 'late';
                        $paidDate = null;
                    }
                }

                $records[] = [
                    'student_id' => $sid,
                    'month'      => $month,
                    'amount'     => 1500.00,
                    'due_date'   => $dueDate,
                    'paid_date'  => $paidDate,
                    'status'     => $status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('payments')->insert($chunk);
        }

        // Sync student fee_status from current month's payment
        $curMonth = now()->format('Y-m');
        $payments = DB::table('payments')->where('month', $curMonth)->get();
        foreach ($payments as $pay) {
            DB::table('students')
                ->where('id', $pay->student_id)
                ->where('fee_status', '!=', 'exempt')
                ->update(['fee_status' => $pay->status]);
        }

        $this->command->info("   → " . count($records) . " payment records (6 months)");
    }

    /* ───────────────── announcements ───────────────── */

    private function createAnnouncements(): void
    {
        $admin = Admin::first();
        if (! $admin) {
            return;
        }

        $items = [
            ['مرحباً بكم في المدرسة القرآنية',          'نرحب بجميع الطلاب وأولياء الأمور في بداية الفصل الدراسي الجديد. نسأل الله التوفيق والسداد للجميع.',                                   ['all'],              null],
            ['تذكير بموعد دفع الاشتراك الشهري',          'نذكر أولياء الأمور الكرام بضرورة دفع الاشتراك الشهري قبل تاريخ 10 من كل شهر. يرجى التواصل مع الإدارة في حال وجود أي استفسار.',    ['parent'],            Carbon::now()->addMonths(3)->toDateString()],
            ['اجتماع المعلمين الشهري',                   'يُعقد الاجتماع الشهري للمعلمين يوم الأحد القادم في الساعة 09:00 صباحاً في قاعة الاجتماعات. الحضور إلزامي.',                        ['teacher'],           Carbon::now()->addWeeks(2)->toDateString()],
            ['مسابقة حفظ القرآن الكريم السنوية',          'يسرنا الإعلان عن مسابقة حفظ القرآن الكريم السنوية. الفئات: 5 أجزاء، 10 أجزاء، 15 جزءاً، القرآن كاملاً.',                       ['all'],              Carbon::now()->addMonths(2)->toDateString()],
            ['عطلة عيد الفطر المبارك',                   'نعلم الجميع أن المدرسة ستكون في عطلة بمناسبة عيد الفطر المبارك. كل عام وأنتم بخير.',                                           ['all'],              '2026-04-06'],
            ['تحديث جدول الحلقات',                       'تم تحديث جدول بعض الحلقات بسبب إعادة توزيع القاعات. يرجى مراجعة الجدول الجديد من خلال التطبيق.',                                 ['teacher','parent'], null],
            ['نتائج الامتحان الفصلي',                    'تم نشر نتائج الامتحان الفصلي الأول. يمكن لأولياء الأمور الاطلاع على نتائج أبنائهم من خلال التطبيق.',                              ['parent'],            Carbon::now()->addMonth()->toDateString()],
            ['ورشة تجويد متقدمة للمعلمين',                'تُعقد ورشة عمل في أحكام التجويد المتقدمة يوم الخميس القادم بإشراف الشيخ عبد الرحمن.',                                         ['teacher'],           Carbon::now()->addWeeks(1)->toDateString()],
            ['حملة نظافة المدرسة',                       'ندعو الجميع للمشاركة في حملة نظافة المدرسة يوم السبت القادم من الساعة 08:00 إلى 12:00.',                                        ['all'],              Carbon::now()->addWeeks(1)->toDateString()],
            ['توزيع الجوائز على المتفوقين',               'سيتم توزيع الجوائز على الطلاب المتفوقين في حفل خاص بحضور أولياء الأمور يوم الأحد القادم.',                                    ['all'],              Carbon::now()->addWeeks(2)->toDateString()],
            ['فتح باب التسجيل للطلاب الجدد',             'باب التسجيل مفتوح للطلاب الجدد للفصل الثاني. يرجى إحضار شهادة الميلاد وصورتين شمسيتين.',                                      ['parent'],            Carbon::now()->addMonths(2)->toDateString()],
            ['رحلة ترفيهية للطلاب المتفوقين',            'تنظم المدرسة رحلة ترفيهية للطلاب المتفوقين يوم الجمعة القادم إلى حديقة التجارب.',                                             ['parent'],            Carbon::now()->addWeeks(1)->toDateString()],
            ['تقييم أداء المعلمين الفصلي',               'سيتم إجراء تقييم دوري لأداء المعلمين خلال الأسابيع القادمة. يرجى تحضير تقارير الحلقات.',                                       ['teacher'],           Carbon::now()->addWeeks(3)->toDateString()],
            ['إعلان عن وظائف شاغرة',                     'نبحث عن معلمين ذوي خبرة في تحفيظ القرآن الكريم والتجويد. يرجى إرسال السيرة الذاتية.',                                          ['all'],              Carbon::now()->addMonths(1)->toDateString()],
            ['صيانة المبنى الرئيسي',                     'سيتم إجراء أعمال صيانة في المبنى الرئيسي خلال عطلة نهاية الأسبوع. قد تُنقل بعض الحلقات إلى الملحق.',                           ['teacher','parent'], Carbon::now()->addDays(5)->toDateString()],
        ];

        foreach ($items as $a) {
            Announcement::firstOrCreate(
                ['title' => $a[0]],
                ['created_by' => $admin->id, 'content' => $a[1], 'target_roles' => $a[2], 'expiry_date' => $a[3]]
            );
        }

        $this->command->info("   → " . count($items) . " announcements");
    }

    /* ───────────────── rankings ───────────────── */

    private function calculateRankings(array $halaqat, array $studentsByHalaqa): void
    {
        $periods = [
            ['2026-03-01', '2026-03-31'],
            ['2026-04-01', '2026-04-30'],
            ['2026-05-01', '2026-05-31'],
        ];

        $count = 0;

        foreach ($halaqat as $halaqa) {
            $studentIds = $studentsByHalaqa[$halaqa->id] ?? [];
            if (empty($studentIds)) {
                continue;
            }

            foreach ($periods as [$pStart, $pEnd]) {
                $scores = [];
                foreach ($studentIds as $sid) {
                    $scores[] = [
                        'student_id' => $sid,
                        'score'      => Ranking::calculateForStudent($sid, $halaqa->id, $pStart, $pEnd),
                    ];
                }

                usort($scores, fn ($a, $b) => $b['score'] <=> $a['score']);

                foreach ($scores as $rank => $entry) {
                    Ranking::updateOrCreate(
                        [
                            'student_id'   => $entry['student_id'],
                            'halaqa_id'    => $halaqa->id,
                            'period_type'  => 'monthly',
                            'period_start' => $pStart,
                            'period_end'   => $pEnd,
                        ],
                        [
                            'score'         => $entry['score'],
                            'rank'          => $rank + 1,
                            'calculated_at' => now(),
                        ]
                    );
                    $count++;
                }
            }
        }

        $this->command->info("   → {$count} ranking entries (3 months × " . count($halaqat) . " halaqat)");
    }
}
