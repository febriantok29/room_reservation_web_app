<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            [
                'id'          => 'DIV-01',
                'name'        => 'Divisi Teknologi Informasi',
                'code'        => 'IT',
                'description' => 'Divisi yang menangani infrastruktur IT, pengembangan sistem, dan dukungan teknis.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-02',
                'name'        => 'Divisi HCM',
                'code'        => 'HCM',
                'description' => 'Human Capital Management: rekrutmen, payroll, dan pengembangan karyawan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-03',
                'name'        => 'Divisi Keuangan',
                'code'        => 'KEU',
                'description' => 'Divisi yang menangani keuangan, akuntansi, dan pelaporan keuangan perusahaan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-04',
                'name'        => 'Divisi Pengembangan Bisnis',
                'code'        => 'PDB',
                'description' => 'Divisi yang menjembatani strategi bisnis, kemitraan, dan pengembangan layanan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-05',
                'name'        => 'Divisi Operasi',
                'code'        => 'OPS',
                'description' => 'Divisi yang mengelola kegiatan operasional harian dan logistik perusahaan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-06',
                'name'        => 'Lembaga Kursus dan Pelatihan',
                'code'        => 'LKP',
                'description' => 'Divisi Lembaga Kursus dan Pelatihan (LKP).',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-07',
                'name'        => 'Divisi K3',
                'code'        => 'K3',
                'description' => 'Keselamatan dan Kesehatan Kerja (K3).',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-08',
                'name'        => 'Divisi Umum',
                'code'        => 'UMU',
                'description' => 'Divisi Umum: rumah tangga, sarana prasarana, dan administrasi umum.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-09',
                'name'        => 'Sekretaris Direksi',
                'code'        => 'SD',
                'description' => 'Divisi Sekretaris Direksi.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
        ];

        DB::table('m_divisions')->insert($divisions);

        $this->command->info('Divisions seeded: 9 divisions (IT, HCM, KEU, PDB, OPS, LKP, K3, UMU, SD)');
    }
}
