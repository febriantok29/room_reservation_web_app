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
            // Divisi IT (DIV-01) - tim + pemilik + dosen
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00009',
                'division_id' => 'DIV-01',
                'email' => 'febriantok29@gmail.com',
                'password' => $demoPassword,
                'first_name' => 'Febrianto K',
                'last_name' => '',
                'date_of_birth' => '2000-02-29',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00010',
                'division_id' => 'DIV-01',
                'email' => 'ardi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Ardi',
                'last_name' => '',
                'date_of_birth' => '1995-05-10',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00011',
                'division_id' => 'DIV-01',
                'email' => 'musa@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Musa',
                'last_name' => '',
                'date_of_birth' => '1994-11-22',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00012',
                'division_id' => 'DIV-01',
                'email' => 'irpan@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Irpan',
                'last_name' => '',
                'date_of_birth' => '1993-08-14',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00013',
                'division_id' => 'DIV-01',
                'email' => 'faldi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Faldi',
                'last_name' => '',
                'date_of_birth' => '1996-02-02',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00014',
                'division_id' => 'DIV-01',
                'email' => 'gilang@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Gilang',
                'last_name' => '',
                'date_of_birth' => '1997-06-19',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'IT-2020-00015',
                'division_id' => 'DIV-01',
                'email' => 'temi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Temi',
                'last_name' => '',
                'date_of_birth' => '1995-10-30',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Dosen Pembimbing & Penguji (tambahan)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'DS-001',
                'division_id' => 'DIV-01',
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
                'division_id' => 'DIV-01',
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
                'division_id' => 'DIV-01',
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
                'division_id' => 'DIV-01',
                'email' => 'wawan.kurniawan@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Wawan',
                'last_name' => 'Kurniawan, S.Kom, M.Kom',
                'date_of_birth' => '1976-12-05',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi HCM (DIV-02)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HCM-001',
                'division_id' => 'DIV-02',
                'email' => 'puti@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Puti',
                'last_name' => '',
                'date_of_birth' => '1992-01-10',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HCM-002',
                'division_id' => 'DIV-02',
                'email' => 'elza@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Elza',
                'last_name' => '',
                'date_of_birth' => '1993-03-21',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HCM-003',
                'division_id' => 'DIV-02',
                'email' => 'iqbal@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Iqbal',
                'last_name' => '',
                'date_of_birth' => '1991-07-04',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HCM-004',
                'division_id' => 'DIV-02',
                'email' => 'akbar@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Akbar',
                'last_name' => '',
                'date_of_birth' => '1990-09-15',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HCM-005',
                'division_id' => 'DIV-02',
                'email' => 'fariz@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Fariz',
                'last_name' => '',
                'date_of_birth' => '1994-12-02',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'HCM-006',
                'division_id' => 'DIV-02',
                'email' => 'fifi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Bu Fifi',
                'last_name' => '',
                'date_of_birth' => '1985-05-19',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Keuangan (DIV-03)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-001',
                'division_id' => 'DIV-03',
                'email' => 'tifa@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Tifa',
                'last_name' => '',
                'date_of_birth' => '1993-02-08',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-002',
                'division_id' => 'DIV-03',
                'email' => 'wili@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Wili',
                'last_name' => '',
                'date_of_birth' => '1992-06-25',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-003',
                'division_id' => 'DIV-03',
                'email' => 'arda@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Arda',
                'last_name' => '',
                'date_of_birth' => '1991-11-11',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-004',
                'division_id' => 'DIV-03',
                'email' => 'rafi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Rafi',
                'last_name' => '',
                'date_of_birth' => '1995-04-17',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-005',
                'division_id' => 'DIV-03',
                'email' => 'faiz@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Faiz',
                'last_name' => '',
                'date_of_birth' => '1996-08-23',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-006',
                'division_id' => 'DIV-03',
                'email' => 'lia@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Bu Lia',
                'last_name' => '',
                'date_of_birth' => '1984-09-30',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'KEU-007',
                'division_id' => 'DIV-03',
                'email' => 'sofia@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Sofia',
                'last_name' => '',
                'date_of_birth' => '1994-01-28',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Pengembangan Bisnis (DIV-04)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'PDB-001',
                'division_id' => 'DIV-04',
                'email' => 'yuni@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Yuni',
                'last_name' => '',
                'date_of_birth' => '1992-05-05',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'PDB-002',
                'division_id' => 'DIV-04',
                'email' => 'ayu@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Ayu',
                'last_name' => '',
                'date_of_birth' => '1993-09-12',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'PDB-003',
                'division_id' => 'DIV-04',
                'email' => 'nia@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Bu Nia',
                'last_name' => '',
                'date_of_birth' => '1986-03-08',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Operasi (DIV-05)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-001',
                'division_id' => 'DIV-05',
                'email' => 'ko@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Ko',
                'last_name' => '',
                'date_of_birth' => '1989-07-07',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-002',
                'division_id' => 'DIV-05',
                'email' => 'fanisa@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Fanisa',
                'last_name' => '',
                'date_of_birth' => '1994-02-18',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-003',
                'division_id' => 'DIV-05',
                'email' => 'anisa@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Anisa',
                'last_name' => '',
                'date_of_birth' => '1995-10-09',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-004',
                'division_id' => 'DIV-05',
                'email' => 'munzir@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Munzir',
                'last_name' => '',
                'date_of_birth' => '1991-12-01',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-005',
                'division_id' => 'DIV-05',
                'email' => 'abdi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Abdi',
                'last_name' => '',
                'date_of_birth' => '1993-04-14',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-006',
                'division_id' => 'DIV-05',
                'email' => 'galih@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Galih',
                'last_name' => '',
                'date_of_birth' => '1982-06-21',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-007',
                'division_id' => 'DIV-05',
                'email' => 'hendra@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Hendra',
                'last_name' => '',
                'date_of_birth' => '1981-09-03',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'OPS-008',
                'division_id' => 'DIV-05',
                'email' => 'firman@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Firman',
                'last_name' => '',
                'date_of_birth' => '1980-11-27',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Lembaga Kursus dan Pelatihan (DIV-06)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'LKP-001',
                'division_id' => 'DIV-06',
                'email' => 'deden@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Deden',
                'last_name' => '',
                'date_of_birth' => '1983-03-15',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'LKP-002',
                'division_id' => 'DIV-06',
                'email' => 'erza@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Erza',
                'last_name' => '',
                'date_of_birth' => '1996-07-22',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'LKP-003',
                'division_id' => 'DIV-06',
                'email' => 'agus.firmansyah@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Agus',
                'last_name' => 'Firmansyah',
                'date_of_birth' => '1979-01-30',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi K3 (DIV-07)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'K3-001',
                'division_id' => 'DIV-07',
                'email' => 'mail@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Mail',
                'last_name' => '',
                'date_of_birth' => '1990-08-08',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'K3-002',
                'division_id' => 'DIV-07',
                'email' => 'pupu@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Pupu',
                'last_name' => '',
                'date_of_birth' => '1978-05-17',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Divisi Umum (DIV-08)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'UMU-001',
                'division_id' => 'DIV-08',
                'email' => 'karlina@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Bu Karlina',
                'last_name' => '',
                'date_of_birth' => '1981-02-11',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'UMU-002',
                'division_id' => 'DIV-08',
                'email' => 'syamsul@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Syamsul',
                'last_name' => '',
                'date_of_birth' => '1977-10-05',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'UMU-003',
                'division_id' => 'DIV-08',
                'email' => 'suyud@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Pak Suyud',
                'last_name' => '',
                'date_of_birth' => '1979-06-14',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'UMU-004',
                'division_id' => 'DIV-08',
                'email' => 'ega@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Ega',
                'last_name' => '',
                'date_of_birth' => '1995-03-03',
                'is_admin' => false,
                'is_active' => true,
            ],

            // ========================================
            // Sekretaris Direksi (DIV-09)
            // ========================================
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'SD-001',
                'division_id' => 'DIV-09',
                'email' => 'wendi@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Wendi',
                'last_name' => '',
                'date_of_birth' => '1993-11-19',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'SD-002',
                'division_id' => 'DIV-09',
                'email' => 'fina@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Fina',
                'last_name' => '',
                'date_of_birth' => '1994-07-07',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'SD-003',
                'division_id' => 'DIV-09',
                'email' => 'farah@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Farah',
                'last_name' => '',
                'date_of_birth' => '1992-09-25',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => (string) Str::uuid7(),
                'employee_id' => 'SD-004',
                'division_id' => 'DIV-09',
                'email' => 'indri@rapatrack.com',
                'password' => $demoPassword,
                'first_name' => 'Bu Indri',
                'last_name' => '',
                'date_of_birth' => '1983-04-09',
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

        $this->command->info('Users seeded: 50 total (2 admins + 48 staff/dosen across 9 divisions)');
        $this->command->info('
========================================
LOGIN DEMO (password semua: password):
========================================
1. Admin System : admin01

2. Pemilik (IT)  : IT-2020-00009  (Febrianto K)
   Rekan IT      : Ardi, Musa, Irpan, Faldi, Gilang, Temi

3. Dosen Penguji (IT) : DS-001 s.d DS-004
   - handrie.noprisson, henri.septanto, giri.purnama, wawan.kurniawan

4. Divisi lain (masing-masing terisi):
   HCM(6) KEU(7) PDB(3) OPS(8) LKP(3) K3(2) UMU(4) SD(4)
========================================
        ');
    }
}
