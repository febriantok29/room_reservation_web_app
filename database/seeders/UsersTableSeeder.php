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
        $users = [
            // ========================================
            // Admin Users (3 total, no division)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'admin01',
                'division_id' => null,
                'email' => 'admin@roomreservation.com',
                'password' => Hash::make('Admin@123'),
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
                'email' => 'john.admin@roomreservation.com',
                'password' => Hash::make('Admin@123'),
                'first_name' => 'John',
                'last_name' => 'Administrator',
                'date_of_birth' => '1987-03-20',
                'is_admin' => true,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'ADM-2025-03',
                'division_id' => null,
                'email' => 'emma.admin@roomreservation.com',
                'password' => Hash::make('Admin@123'),
                'first_name' => 'Emma',
                'last_name' => 'Williams',
                'date_of_birth' => '1989-06-12',
                'is_admin' => true,
                'is_active' => true,
            ],

            // ========================================
            // Divisi IT (DIV-01) - 2 staff
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00009',
                'division_id' => 'DIV-01',
                'email' => 'febri.tokan@roomreservation.com',
                'password' => Hash::make('bimbim'),
                'first_name' => 'Febriant',
                'last_name' => 'Oka Nugraha',
                'date_of_birth' => '1998-02-28',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2021-00010',
                'division_id' => 'DIV-01',
                'email' => 'david.johnson@roomreservation.com',
                'password' => Hash::make('Staff@123'),
                'first_name' => 'David',
                'last_name' => 'Johnson',
                'date_of_birth' => '1992-08-25',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi HRD (DIV-02) - 2 staff (incl. required accounts)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HRD-2019-00001',
                'division_id' => 'DIV-02',
                'email' => 'dosen.pembimbing@roomreservation.com',
                'password' => Hash::make('dosenn'),
                'first_name' => 'Dr. Budi',
                'last_name' => 'Santoso, M.Kom',
                'date_of_birth' => '1975-03-22',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HRD-2019-00002',
                'division_id' => 'DIV-02',
                'email' => 'rina.hrd@roomreservation.com',
                'password' => Hash::make('dosenn'),
                'first_name' => 'Rina',
                'last_name' => 'Setiawati',
                'date_of_birth' => '1980-07-15',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Finance (DIV-03) - 2 staff
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'FIN-2020-00005',
                'division_id' => 'DIV-03',
                'email' => 'michael.wilson@roomreservation.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Michael',
                'last_name' => 'Wilson',
                'date_of_birth' => '1988-07-15',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'FIN-2021-00012',
                'division_id' => 'DIV-03',
                'email' => 'jennifer.davis@roomreservation.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Jennifer',
                'last_name' => 'Davis',
                'date_of_birth' => '1993-12-05',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Marketing (DIV-04) - 1 staff
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'MKT-2020-00007',
                'division_id' => 'DIV-04',
                'email' => 'robert.miller@roomreservation.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Robert',
                'last_name' => 'Miller',
                'date_of_birth' => '1991-04-22',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Operasional (DIV-05) - 2 staff
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-2020-00006',
                'division_id' => 'DIV-05',
                'email' => 'maria.garcia@roomreservation.com',
                'password' => Hash::make('Staff@123'),
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'date_of_birth' => '1990-05-10',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-2021-00011',
                'division_id' => 'DIV-05',
                'email' => 'christopher.martin@roomreservation.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Christopher',
                'last_name' => 'Martin',
                'date_of_birth' => '1993-03-16',
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

        $this->command->info('Users seeded: 12 total (3 admins + 9 staff across 5 divisions)');
        $this->command->info('
========================================
REQUIRED LOGIN CREDENTIALS:
========================================
1. Admin System:
   Employee ID: admin01
   Password: Admin@123

2. User Personal (IT Division):
   Employee ID: IT-2020-00009
   Email: febri.tokan@roomreservation.com
   Password: bimbim

3. Lecturer/Supervisor (HRD):
   Employee ID: HRD-2019-00001
   Email: dosen.pembimbing@roomreservation.com
   Password: dosen

Additional accounts:
   - HRD-2019-00002 (password: dosen)
   - ADM-2025-03 (admin, password: Admin@123)
========================================
        ');
    }
}
