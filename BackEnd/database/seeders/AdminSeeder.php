<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Créer le compte Account pour l'admin
        $account = Account::firstOrCreate(
            ['email' => 'admin@school.com'],
            [
                // password cast 'hashed' dans Account model
                // → bcrypt automatique à l'assignation
                'password'  => 'Admin@1234',
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        // Créer le profil Admin lié au compte
        Admin::firstOrCreate(
            ['account_id' => $account->id],
            [
                'name'        => 'Super Admin',
            ]
        );

        $this->command->info('✅ AdminSeeder: super admin créé → admin@school.com / Admin@1234');
    }
}