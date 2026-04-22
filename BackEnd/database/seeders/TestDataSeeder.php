<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Halaqa;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Announcement;
use App\Models\Admin;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // ════════════════════════════════════════
        // 2 TEACHERS
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
        // 2 HALAQAT
        // ════════════════════════════════════════

        $halaqa1 = Halaqa::firstOrCreate(
            ['name' => 'حلقة الفجر'],
            [
                'teacher_id'   => $teacher1->id,
                'schedule'     => 'Samedi & Dimanche 9h-11h',
                'max_students' => 20,
                'is_active'    => true,
            ]
        );

        $halaqa2 = Halaqa::firstOrCreate(
            ['name' => 'حلقة النور'],
            [
                'teacher_id'   => $teacher2->id,
                'schedule'     => 'Vendredi 14h-16h',
                'max_students' => 15,
                'is_active'    => true,
            ]
        );

        // ════════════════════════════════════════
        // 3 PARENTS
        // ════════════════════════════════════════

        $parent1Account = Account::firstOrCreate(
            ['email' => 'parent1@school.com'],
            [
                'password'  => 'Parent@1234',
                'role'      => 'parent',
                'is_active' => true,
            ]
        );

        $parent1 = ParentProfile::firstOrCreate(
            ['account_id' => $parent1Account->id],
            [
                'name'       => 'Mohamed Mansouri',
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

        $parent2 = ParentProfile::firstOrCreate(
            ['account_id' => $parent2Account->id],
            [
                'name'       => 'Karim Boudiaf',
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

        $parent3 = ParentProfile::firstOrCreate(
            ['account_id' => $parent3Account->id],
            [
                'name'       => 'Ali Cherif',
                'occupation' => 'Enseignant',
                'address'    => 'Constantine',
            ]
        );

        // ════════════════════════════════════════
        // 6 STUDENTS
        // ════════════════════════════════════════

        // Parent 1 → 2 enfants dans halaqa 1
        Student::firstOrCreate(
            [
                'full_name' => 'Ahmed Mansouri',
                'parent_id' => $parent1->id,
            ],
            [
                'halaqa_id'       => $halaqa1->id,
                'birth_date'      => '2012-03-15',
                'social_state'    => 'normal',
                'fee_status'      => 'paid',
            ]
        );

        Student::firstOrCreate(
            [
                'full_name' => 'Sara Mansouri',
                'parent_id' => $parent1->id,
            ],
            [
                'halaqa_id'       => $halaqa1->id,
                'birth_date'      => '2014-07-20',
                'social_state'    => 'normal',
                'fee_status'      => 'paid',
            ]
        );

        // Parent 2 → 2 enfants (1 exempt car père décédé)
        Student::firstOrCreate(
            [
                'full_name' => 'Hamza Boudiaf',
                'parent_id' => $parent2->id,
            ],
            [
                'halaqa_id'       => $halaqa1->id,
                'birth_date'      => '2011-11-05',
                'social_state'    => 'father_deceased',
                'fee_status'      => 'exempt',
            ]
        );

        Student::firstOrCreate(
            [
                'full_name' => 'Lina Boudiaf',
                'parent_id' => $parent2->id,
            ],
            [
                'halaqa_id'       => $halaqa2->id,
                'birth_date'      => '2013-04-18',
                'social_state'    => 'normal',
                'fee_status'      => 'pending',
            ]
        );

        // Parent 3 → 2 enfants (1 en retard, 1 sans halaqa)
        Student::firstOrCreate(
            [
                'full_name' => 'Youssef Cherif',
                'parent_id' => $parent3->id,
            ],
            [
                'halaqa_id'       => $halaqa2->id,
                'birth_date'      => '2010-08-30',
                'social_state'    => 'divorced_parents',
                'fee_status'      => 'late',
            ]
        );

        Student::firstOrCreate(
            [
                'full_name' => 'Fatima Cherif',
                'parent_id' => $parent3->id,
            ],
            [
                // halaqa_id null = pas encore assignée
                'halaqa_id'       => null,
                'birth_date'      => '2015-01-12',
                'social_state'    => 'normal',
                'fee_status'      => 'pending',
            ]
        );

        $this->command->info('✅ TestDataSeeder: 2 teachers, 2 halaqat, 3 parents, 6 students créés');
        $this->command->info('   Teacher 1: teacher1@school.com / Teacher@1234');
        $this->command->info('   Teacher 2: teacher2@school.com / Teacher@1234');
        $this->command->info('   Parent  1: parent1@school.com  / Parent@1234');
        $this->command->info('   Parent  2: parent2@school.com  / Parent@1234');
        $this->command->info('   Parent  3: parent3@school.com  / Parent@1234');
        $studentIds = Student::pluck('id');
foreach ($studentIds as $sid) {
    Payment::firstOrCreate(
        ['student_id' => $sid, 'month' => '2025-03'],
        [
            'amount'   => 1500.00,
            'due_date' => '2025-03-05',
            'status'   => 'paid',
            'paid_date'=> '2025-03-03',
        ]
    );
    Payment::firstOrCreate(
        ['student_id' => $sid, 'month' => '2025-04'],
        [
            'amount'   => 1500.00,
            'due_date' => '2025-04-05',
            'status'   => 'pending',
            'paid_date'=> null,
        ]
    );
}

// ── ANNOUNCEMENTS ───────────────────────────
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
            'content'      => 'نذكر أولياء الأمور بضرورة دفع اشتراك شهر أبريل قبل تاريخ 10/04/2025.',
            'target_roles' => ['parent'],
            'expiry_date'  => '2025-04-30',
        ]
    );
}

$this->command->info('✅ Payments + Announcements créés');
    }

    
}