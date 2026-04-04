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
                'name'        => 'Divisi Operasi',
                'code'        => 'OPS',
                'description' => 'Divisi yang menangani kegiatan operasional perusahaan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-02',
                'name'        => 'Divisi Keuangan dan Pajak',
                'code'        => 'KNP',
                'description' => 'Divisi yang menangani keuangan, akuntansi, dan perpajakan perusahaan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-03',
                'name'        => 'Divisi Human Resource',
                'code'        => 'HRD',
                'description' => 'Divisi yang mengelola sumber daya manusia dan pengembangan karyawan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
        ];

        DB::table('m_divisions')->insert($divisions);

        $this->command->info('Divisions seeded: DIV-01 (OPS), DIV-02 (KNP), DIV-03 (HRD)');
    }
}
