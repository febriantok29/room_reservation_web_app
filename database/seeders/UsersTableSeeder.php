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
            // Admin Users
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00001',
                'email' => 'admin@roomreservation.com',
                'password' => Hash::make('Admin@123'),
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'date_of_birth' => '1985-01-15',
                'role' => 'admin',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00002',
                'email' => 'john.admin@roomreservation.com',
                'password' => Hash::make('Admin@123'),
                'first_name' => 'John',
                'last_name' => 'Administrator',
                'date_of_birth' => '1987-03-20',
                'role' => 'admin',
            ],

            // Staff Users (for approval)
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00003',
                'email' => 'staff1@roomreservation.com',
                'password' => Hash::make('Staff@123'),
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'date_of_birth' => '1990-05-10',
                'role' => 'staff',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00004',
                'email' => 'staff2@roomreservation.com',
                'password' => Hash::make('Staff@123'),
                'first_name' => 'David',
                'last_name' => 'Johnson',
                'date_of_birth' => '1992-08-25',
                'role' => 'staff',
            ],

            // Regular Users (employees)
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00005',
                'email' => 'sarah.brown@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Sarah',
                'last_name' => 'Brown',
                'date_of_birth' => '1990-10-31',
                'role' => 'user',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00006',
                'email' => 'michael.wilson@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Michael',
                'last_name' => 'Wilson',
                'date_of_birth' => '1988-07-15',
                'role' => 'user',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00007',
                'email' => 'jennifer.davis@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Jennifer',
                'last_name' => 'Davis',
                'date_of_birth' => '1993-12-05',
                'role' => 'user',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00008',
                'email' => 'robert.miller@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Robert',
                'last_name' => 'Miller',
                'date_of_birth' => '1991-04-22',
                'role' => 'user',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00009',
                'email' => 'lisa.anderson@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'Lisa',
                'last_name' => 'Anderson',
                'date_of_birth' => '1989-09-18',
                'role' => 'user',
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'EMP-2025-00010',
                'email' => 'william.taylor@example.com',
                'password' => Hash::make('User@123'),
                'first_name' => 'William',
                'last_name' => 'Taylor',
                'date_of_birth' => '1994-02-28',
                'role' => 'user',
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
  Password: Admin@123

Staff:
  Email: staff1@roomreservation.com
  Password: Staff@123

User:
  Email: sarah.brown@example.com
  Password: User@123
========================================
        ');
    }
}
