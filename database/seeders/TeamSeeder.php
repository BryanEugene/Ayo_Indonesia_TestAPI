<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'nama_tim' => 'Persija Jakarta',
                'logo_tim' => 'https://seeklogo.com/vector-logo/107803/persija-jakarta',
                'tahun_berdiri' => 1928,
                'alamat_markas' => 'Stadion Gelora Bung Karno, Jakarta',
                'kota_markas' => 'Jakarta',
            ],
            [
                'nama_tim' => 'Persib Bandung',
                'logo_tim' => 'https://example.com/logos/persib.png',
                'tahun_berdiri' => 1933,
                'alamat_markas' => 'Stadion Gelora Bandung Lautan Api, Bandung',
                'kota_markas' => 'Bandung',
            ],
            [
                'nama_tim' => 'Arema FC',
                'logo_tim' => 'https://example.com/logos/arema.png',
                'tahun_berdiri' => 1987,
                'alamat_markas' => 'Stadion Kanjuruhan, Malang',
                'kota_markas' => 'Malang',
            ],
            [
                'nama_tim' => 'Bali United',
                'logo_tim' => 'https://example.com/logos/bali.png',
                'tahun_berdiri' => 2014,
                'alamat_markas' => 'Stadion Kapten I Wayan Dipta, Denpasar',
                'kota_markas' => 'Denpasar',
            ],
            [
                'nama_tim' => 'PSM Makassar',
                'logo_tim' => 'https://example.com/logos/psm.png',
                'tahun_berdiri' => 1915,
                'alamat_markas' => 'Stadion Gelora BJ Habibie, Makassar',
                'kota_markas' => 'Makassar',
            ],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
