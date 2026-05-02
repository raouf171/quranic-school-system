<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\DateEntry;
use App\Models\Halaqa;
use App\Models\ParentProfile;
use App\Models\Payment;
use App\Models\Seance;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // ════════════════════════════════════════
        // 2 TEACHERS (noms / emails inchangés)
        // ════════════════════════════════════════

        $teacher1Account = Account::firstOrCreate(
            ['email' => 'teacher1@school.com'],
            [
                'password'  => 'Teacher@1234',
                'role'      => 'teacher',
                'is_active' => true,
            ]
        );

        $teacher1 = Teacher::firstOrCreate(
            ['account_id' => $teacher1Account->id],
            [
                'name'         => 'Omar Benali',
                'hiring_date'  => '2022-09-01',
                'is_available' => true,
            ]
        );

        $teacher2Account = Account::firstOrCreate(
            ['email' => 'teacher2@school.com'],
            [
                'password'  => 'Teacher@1234',
                'role'      => 'teacher',
                'is_active' => true,
            ]
        );

        $teacher2 = Teacher::firstOrCreate(
            ['account_id' => $teacher2Account->id],
            [
                'name'         => 'Youssef Hamdi',
                'hiring_date'  => '2023-01-15',
                'is_available' => true,
            ]
        );

        // ════════════════════════════════════════
        // CLASSROOMS (pour séances / next-seance)
        // ════════════════════════════════════════

        $classroomA = Classroom::firstOrCreate(
            ['name' => 'Salle A'],
            [
                'building'     => 'Bâtiment Principal',
                'capacity'     => 25,
                'is_available' => true,
            ]
        );

        $classroomB = Classroom::firstOrCreate(
            ['name' => 'Salle B'],
            [
                'building'     => 'Bâtiment Principal',
                'capacity'     => 20,
                'is_available' => true,
            ]
        );

        $classroomC = Classroom::firstOrCreate(
            ['name' => 'Salle C'],
            [
                'building'     => 'Annexe',
                'capacity'     => 15,
                'is_available' => true,
            ]
        );

        // ════════════════════════════════════════
        // 2 HALAQAT (noms inchangés)
        // ════════════════════════════════════════

        $halaqa1 = Halaqa::updateOrCreate(
            ['name' => 'حلقة الفجر'],
            [
                'teacher_id'   => $teacher1->id,
                'gender'       => 'female',
                'schedule'     => 'Samedi & Dimanche 9h-11h',
                'max_students' => 20,
                'is_active'    => true,
            ]
        );

        $halaqa2 = Halaqa::updateOrCreate(
            ['name' => 'حلقة النور'],
            [
                'teacher_id'   => $teacher2->id,
                'gender'       => 'male',
                'schedule'     => 'Vendredi 14h-16h',
                'max_students' => 15,
                'is_active'    => true,
            ]
        );

        // ════════════════════════════════════════
        // 3 PARENTS (noms / emails inchangés)
        // ════════════════════════════════════════

        $parent1Account = Account::firstOrCreate(
            ['email' => 'parent1@school.com'],
            [
                'password'  => 'Parent@1234',
                'role'      => 'parent',
                'is_active' => true,
            ]
        );

        $parent1 = ParentProfile::updateOrCreate(
            ['account_id' => $parent1Account->id],
            [
                'name'       => 'Mohamed Mansouri',
                'phone'      => '+213555000001',
                'occupation' => 'Ingénieur',
                'address'    => 'Alger',
            ]
        );

        $parent2Account = Account::firstOrCreate(
            ['email' => 'parent2@school.com'],
            [
                'password'  => 'Parent@1234',
                'role'      => 'parent',
                'is_active' => true,
            ]
        );

        $parent2 = ParentProfile::updateOrCreate(
            ['account_id' => $parent2Account->id],
            [
                'name'       => 'Karim Boudiaf',
                'phone'      => '+213555000002',
                'occupation' => 'Médecin',
                'address'    => 'Oran',
            ]
        );

        $parent3Account = Account::firstOrCreate(
            ['email' => 'parent3@school.com'],
            [
                'password'  => 'Parent@1234',
                'role'      => 'parent',
                'is_active' => true,
            ]
        );

        $parent3 = ParentProfile::updateOrCreate(
            ['account_id' => $parent3Account->id],
            [
                'name'       => 'Ali Cherif',
                'phone'      => '+213555000003',
                'occupation' => 'Enseignant',
                'address'    => 'Constantine',
            ]
        );

        // ════════════════════════════════════════
        // 6 STUDENTS (noms inchangés)
        // ════════════════════════════════════════

        Student::updateOrCreate(
            [
                'full_name' => 'Ahmed Mansouri',
                'parent_id' => $parent1->id,
            ],
            [
                'halaqa_id'             => $halaqa2->id,
                'gender'                => 'male',
                'relationship_nature'   => 'father',
                'school_level'          => 'middle_cem',
                'birth_date'            => '2012-03-15',
                'social_state'          => 'normal',
                'fee_status'            => 'paid',
            ]
        );

        Student::updateOrCreate(
            [
                'full_name' => 'Sara Mansouri',
                'parent_id' => $parent1->id,
            ],
            [
                'halaqa_id'             => $halaqa1->id,
                'gender'                => 'female',
                'relationship_nature'   => 'father',
                'school_level'          => 'primary',
                'birth_date'            => '2014-07-20',
                'social_state'          => 'normal',
                'fee_status'            => 'paid',
            ]
        );

        Student::updateOrCreate(
            [
                'full_name' => 'Hamza Boudiaf',
                'parent_id' => $parent2->id,
            ],
            [
                'halaqa_id'             => $halaqa2->id,
                'gender'                => 'male',
                'relationship_nature'   => 'mother',
                'school_level'          => 'high_school',
                'birth_date'            => '2011-11-05',
                'social_state'          => 'father_deceased',
                'fee_status'            => 'exempt',
            ]
        );

        Student::updateOrCreate(
            [
                'full_name' => 'Lina Boudiaf',
                'parent_id' => $parent2->id,
            ],
            [
                'halaqa_id'             => $halaqa1->id,
                'gender'                => 'female',
                'relationship_nature'   => 'mother',
                'school_level'          => 'middle_cem',
                'birth_date'            => '2013-04-18',
                'social_state'          => 'normal',
                'fee_status'            => 'pending',
            ]
        );

        Student::updateOrCreate(
            [
                'full_name' => 'Youssef Cherif',
                'parent_id' => $parent3->id,
            ],
            [
                'halaqa_id'             => $halaqa2->id,
                'gender'                => 'male',
                'relationship_nature'   => 'uncle',
                'school_level'          => 'primary',
                'birth_date'            => '2010-08-30',
                'social_state'          => 'divorced_parents',
                'fee_status'            => 'late',
            ]
        );

        Student::updateOrCreate(
            [
                'full_name' => 'Fatima Cherif',
                'parent_id' => $parent3->id,
            ],
            [
                'halaqa_id'             => null,
                'gender'                => 'female',
                'relationship_nature'   => 'father',
                'school_level'          => 'kindergarten',
                'birth_date'            => '2015-01-12',
                'social_state'          => 'normal',
                'fee_status'            => 'pending',
            ]
        );

        // ════════════════════════════════════════
        // DATES + SÉANCES (futures = tests next_seance / teacher)
        // ════════════════════════════════════════

        $tomorrow = Carbon::tomorrow()->toDateString();
        $inThreeDays = Carbon::today()->addDays(3)->toDateString();
        $nextWeek = Carbon::today()->addWeek()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $dateTomorrow = DateEntry::firstOrCreate(
            ['date_value' => $tomorrow, 'created_by' => $teacher1->id],
            []
        );
        $dateInThree = DateEntry::firstOrCreate(
            ['date_value' => $inThreeDays, 'created_by' => $teacher2->id],
            []
        );
        $dateNextWeek = DateEntry::firstOrCreate(
            ['date_value' => $nextWeek, 'created_by' => $teacher1->id],
            []
        );
        $dateYesterday = DateEntry::firstOrCreate(
            ['date_value' => $yesterday, 'created_by' => $teacher1->id],
            []
        );

        Seance::firstOrCreate(
            [
                'halaqa_id' => $halaqa1->id,
                'date_id'   => $dateTomorrow->id,
            ],
            [
                'created_by'   => $teacher1->id,
                'classroom_id' => $classroomA->id,
                'notes'        => 'Séance test — حلقة الفجر',
            ]
        );

        Seance::firstOrCreate(
            [
                'halaqa_id' => $halaqa1->id,
                'date_id'   => $dateNextWeek->id,
            ],
            [
                'created_by'   => $teacher1->id,
                'classroom_id' => $classroomB->id,
                'notes'        => 'Séance suivante — حلقة الفجر',
            ]
        );

        Seance::firstOrCreate(
            [
                'halaqa_id' => $halaqa2->id,
                'date_id'   => $dateInThree->id,
            ],
            [
                'created_by'   => $teacher2->id,
                'classroom_id' => $classroomC->id,
                'notes'        => 'Séance test — حلقة النور',
            ]
        );

        Seance::firstOrCreate(
            [
                'halaqa_id' => $halaqa1->id,
                'date_id'   => $dateYesterday->id,
            ],
            [
                'created_by'   => $teacher1->id,
                'classroom_id' => $classroomA->id,
                'notes'        => 'Séance passée (historique)',
            ]
        );

        // ════════════════════════════════════════
        // PAYMENTS (par élève — mois cohérents année courante)
        // ════════════════════════════════════════

        $monthPaid = Carbon::now()->subMonth()->format('Y-m');
        $monthPending = Carbon::now()->format('Y-m');

        foreach (Student::pluck('id') as $sid) {
            Payment::firstOrCreate(
                ['student_id' => $sid, 'month' => $monthPaid],
                [
                    'amount'    => 1500.00,
                    'due_date'  => Carbon::now()->subMonth()->day(5)->toDateString(),
                    'status'    => 'paid',
                    'paid_date' => Carbon::now()->subMonth()->day(3)->toDateString(),
                ]
            );

            Payment::firstOrCreate(
                ['student_id' => $sid, 'month' => $monthPending],
                [
                    'amount'    => 1500.00,
                    'due_date'  => Carbon::now()->day(5)->toDateString(),
                    'status'    => 'pending',
                    'paid_date' => null,
                ]
            );
        }

        // ════════════════════════════════════════
        // ANNONCES (AdminSeeder doit avoir tourné avant)
        // ════════════════════════════════════════

        $admin = Admin::first();
        if ($admin) {
            Announcement::firstOrCreate(
                ['title' => 'مرحباً بكم في المدرسة القرآنية'],
                [
                    'created_by'   => $admin->id,
                    'content'      => 'نرحب بجميع الطلاب وأولياء الأمور في بداية الفصل الدراسي الجديد.',
                    'target_roles' => ['all'],
                    'expiry_date'  => null,
                ]
            );

            Announcement::firstOrCreate(
                ['title' => 'تذكير بموعد دفع الاشتراك'],
                [
                    'created_by'   => $admin->id,
                    'content'      => 'نذكر أولياء الأمور بضرورة دفع الاشتراك قبل تاريخ 10 من كل شهر.',
                    'target_roles' => ['parent'],
                    'expiry_date'  => Carbon::now()->addMonths(3)->toDateString(),
                ]
            );

            Announcement::firstOrCreate(
                ['title' => 'اجتماع المعلمين'],
                [
                    'created_by'   => $admin->id,
                    'content'      => 'اجتماع دوري لمناقشة الحلقات والتقارير.',
                    'target_roles' => ['teacher'],
                    'expiry_date'  => Carbon::now()->addMonth()->toDateString(),
                ]
            );
        }

        $this->command->info('✅ TestDataSeeder: teachers, halaqat, parents, students, classrooms, séances, paiements, annonces');
        $this->command->info('   Teacher 1: teacher1@school.com / Teacher@1234');
        $this->command->info('   Teacher 2: teacher2@school.com / Teacher@1234');
        $this->command->info('   Parent  1: parent1@school.com  / Parent@1234');
        $this->command->info('   Parent  2: parent2@school.com  / Parent@1234');
        $this->command->info('   Parent  3: parent3@school.com  / Parent@1234');
        $this->command->info('   Admin:     admin@school.com   / Admin@1234');
    }
}
