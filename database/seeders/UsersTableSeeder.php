<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Password seragam untuk kemudahan demo sidang.
        $demoPassword = Hash::make('password');

        $users = [
            // ========================================
            // Admin Users (2 total, no division)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'admin01',
                'division_id' => null,
                'email' => 'admin@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'date_of_birth' => '1985-01-15',
                'is_admin' => true,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'ADM-2026-02',
                'division_id' => null,
                'email' => 'john.admin@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'John',
                'last_name' => 'Administrator',
                'date_of_birth' => '1987-03-20',
                'is_admin' => true,
                'is_active' => true,
            ],

            // ========================================
            // Mahasiswa / Pemilik Aplikasi (1)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00009',
                'division_id' => 'DIV-01',
                'email' => 'febriantok29@gmail.com',
                'password' => $demoPassword,
                'first_name' => 'Febrianto',
                'last_name' => 'Kabisatullah',
                'date_of_birth' => '2000-02-29',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Dosen Pembimbing & Penguji (4)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'DS-001',
                'division_id' => null,
                'email' => 'handrie.noprisson@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Dr. Handrie',
                'last_name' => 'Noprisson, ST., M.Kom',
                'date_of_birth' => '1975-03-22',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'DS-002',
                'division_id' => null,
                'email' => 'henri.septanto@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Henri',
                'last_name' => 'Septanto, S.Kom., M.Kom.',
                'date_of_birth' => '1978-07-11',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'DS-003',
                'division_id' => null,
                'email' => 'giri.purnama@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Giri',
                'last_name' => 'Purnama, S.Pd., M.Kom',
                'date_of_birth' => '1980-09-30',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'DS-004',
                'division_id' => null,
                'email' => 'wawan.kurniawan@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Wawan',
                'last_name' => 'Kurniawan, S.Kom, M.Kom',
                'date_of_birth' => '1976-12-05',
                'is_admin' => false,
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            DB::table('s_users')->insert(array_merge($user, [
                'created_at' => now(),
                'created_by' => null,
            ]));
        }

        $this->command->info('Users seeded: 7 total (2 admins + 1 mahasiswa + 4 dosen)');
        $this->command->info('
========================================
LOGIN DEMO (password semua: password):
========================================
1. Admin System:
   Employee ID : admin01
   Email       : admin@rapatrack.com

2. Mahasiswa (pemilik):
   Employee ID : IT-2020-00009
   Email       : febriantok29@gmail.com

3. Dosen Pembimbing & Penguji:
   - handrie.noprisson@rapatrack.com  (DS-001)
   - henri.septanto@rapatrack.com     (DS-002)
   - giri.purnama@rapatrack.com       (DS-003)
   - wawan.kurniawan@rapatrack.com    (DS-004)
========================================
        ');
    }
}
