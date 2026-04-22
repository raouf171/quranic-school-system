<?php

namespace App\Factories;

use App\Models\Account;
use App\Models\Admin;
use App\Models\ParentProfile;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class UserFactory
{
    public static function createTeacher(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $account = Account::create([
                'email'     => $data['email'],
                'password'  => $data['password'],
                'role'      => 'teacher',
                'is_active' => true,
            ]);

            $teacher = Teacher::create([
                'account_id'   => $account->id,
                'name'         => $data['name'],
                'hiring_date'  => $data['hiring_date'] ?? null,
                'is_available' => true,
            ]);

            return ['account' => $account, 'teacher' => $teacher];
        });
    }

    public static function createParent(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $account = Account::create([
                'email'     => $data['email'],
                'password'  => $data['password'],
                'role'      => 'parent',
                'is_active' => true,
            ]);

            $parent = ParentProfile::create([
                'account_id' => $account->id,
                'name'       => $data['name'],
                'occupation' => $data['occupation'] ?? null,
                'address'    => $data['address'] ?? null,
            ]);

            return ['account' => $account, 'parent' => $parent];
        });
    }

    public static function createAdmin(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $account = Account::create([
                'email'     => $data['email'],
                'password'  => $data['password'],
                'role'      => 'admin',
                'is_active' => true,
            ]);

            $admin = Admin::create([
                'account_id' => $account->id,
                'name'       => $data['name'],
            ]);

            return ['account' => $account, 'admin' => $admin];
        });
    }

    /**
     * Smoke test for `php artisan tinker`: create teacher, verify link, delete account.
     * Deleting the account cascades to `teachers` (see migration on `teachers.account_id`).
     */
    public static function tinkerSmokeTeacherTest(): void
    {
        $email = sprintf('factory_test_%s@school.com', str_replace('.', '', uniqid('', true)));

        $result = self::createTeacher([
            'name'     => 'Test Teacher Factory',
            'email'    => $email,
            'password' => 'Test@1234',
        ]);

        dump($result['account']->id);
        dump($result['teacher']->id);
        dump($result['teacher']->account_id === $result['account']->id ? 'LINKED_OK' : 'LINK_ERR');

        $result['account']->delete();

        dump('CLEANUP_OK');
    }
}