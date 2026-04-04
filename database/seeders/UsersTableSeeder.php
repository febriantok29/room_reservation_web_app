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
            // Admin Users (no division, ADM-YYYY-NN format)
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

            // Divisi Operasi (OPS)
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-2026-00001',
                'division_id' => 'DIV-01',
                'email' => 'staff1@roomreservation.com',
                'password' => Hash::make('Staff@123'),
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'date_of_birth' => '1990-05-10',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-2026-00002',
                'division_id' => 'DIV-01',
                'email' => 'staff2@roomreservation.com',
                'password' => Hash::make('Staff@123'),
                'first_name' => 'David',
                'last_name' => 'Johnson',
                'date_of_birth' => '1992-08-25',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-2026-00003',
                'division_id' => 'DIV-01',
                'email' => 'sarah.brown@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Sarah',
                'last_name' => 'Brown',
                'date_of_birth' => '1990-10-31',
                'is_admin' => false,
                'is_active' => true,
            ],

            // Divisi Keuangan dan Pajak (KNP)
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KNP-2026-00001',
                'division_id' => 'DIV-02',
                'email' => 'michael.wilson@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Michael',
                'last_name' => 'Wilson',
                'date_of_birth' => '1988-07-15',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KNP-2026-00002',
                'division_id' => 'DIV-02',
                'email' => 'jennifer.davis@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Jennifer',
                'last_name' => 'Davis',
                'date_of_birth' => '1993-12-05',
                'is_admin' => false,
                'is_active' => true,
            ],

            // Divisi Human Resource (HRD)
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HRD-2026-00001',
                'division_id' => 'DIV-03',
                'email' => 'robert.miller@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Robert',
                'last_name' => 'Miller',
                'date_of_birth' => '1991-04-22',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HRD-2026-00002',
                'division_id' => 'DIV-03',
                'email' => 'lisa.anderson@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Lisa',
                'last_name' => 'Anderson',
                'date_of_birth' => '1989-09-18',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HRD-2026-00003',
                'division_id' => 'DIV-03',
                'email' => 'william.taylor@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'William',
                'last_name' => 'Taylor',
                'date_of_birth' => '1994-02-28',
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

        $this->command->info('Users seeded successfully!');
        $this->command->info('
========================================
Login Credentials:
========================================
Admin:
  Email: admin@roomreservation.com
    Employee ID: ADM-2026-01
  Password: Admin@123

Staff (Operasi):
  Email: staff1@roomreservation.com
    Employee ID: OPS-2026-00001
  Password: Staff@123

Staff (Keuangan):
  Email: michael.wilson@example.com
    Employee ID: KNP-2026-00001
  Password: User@123

Staff (HRD):
  Email: robert.miller@example.com
    Employee ID: HRD-2026-00001
  Password: User@123
========================================
        ');
    }
}
