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
                'name'        => 'Divisi Human Resource',
                'code'        => 'HRD',
                'description' => 'Divisi yang mengelola sumber daya manusia, rekrutmen, dan pengembangan karyawan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-03',
                'name'        => 'Divisi Keuangan dan Akuntansi',
                'code'        => 'FIN',
                'description' => 'Divisi yang menangani keuangan, akuntansi, perpajakan, dan pelaporan keuangan perusahaan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-04',
                'name'        => 'Divisi Marketing dan Sales',
                'code'        => 'MKT',
                'description' => 'Divisi yang menangani strategi pemasaran, promosi, dan penjualan produk/layanan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
            [
                'id'          => 'DIV-05',
                'name'        => 'Divisi Operasional',
                'code'        => 'OPS',
                'description' => 'Divisi yang mengelola kegiatan operasional harian dan logistik perusahaan.',
                'created_at'  => now(),
                'created_by'  => null,
            ],
        ];

        DB::table('m_divisions')->insert($divisions);

        $this->command->info('Divisions seeded: 5 divisions (IT, HRD, FIN, MKT, OPS)');
    }
}
