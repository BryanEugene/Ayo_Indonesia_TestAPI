<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua tim yang ada
        $teams = Team::all();

        if ($teams->isEmpty()) {
            $this->command->warn('Tidak ada tim yang ditemukan. Jalankan TeamSeeder terlebih dahulu.');
            return;
        }

        $players = [
            // Persija Jakarta (team_id = 1)
            [
                'team_id' => 1,
                'nama_pemain' => 'Andritany Ardhiyasa',
                'tinggi_badan' => 183,
                'berat_badan' => 75,
                'posisi_pemain' => 'Penjaga Gawang',
                'nomor_punggung' => 1,
            ],
            [
                'team_id' => 1,
                'nama_pemain' => 'Rizky Dwi Febrianto',
                'tinggi_badan' => 178,
                'berat_badan' => 72,
                'posisi_pemain' => 'Bertahan',
                'nomor_punggung' => 2,
            ],
            [
                'team_id' => 1,
                'nama_pemain' => 'Marko Simic',
                'tinggi_badan' => 190,
                'berat_badan' => 85,
                'posisi_pemain' => 'Bertahan',
                'nomor_punggung' => 5,
            ],
            [
                'team_id' => 1,
                'nama_pemain' => 'Rohit Chand',
                'tinggi_badan' => 175,
                'berat_badan' => 70,
                'posisi_pemain' => 'Gelandang',
                'nomor_punggung' => 8,
            ],
            [
                'team_id' => 1,
                'nama_pemain' => 'Marko Simic',
                'tinggi_badan' => 185,
                'berat_badan' => 80,
                'posisi_pemain' => 'Penyerang',
                'nomor_punggung' => 10,
            ],
            
            // Persib Bandung (team_id = 2)
            [
                'team_id' => 2,
                'nama_pemain' => 'Teja Paku Alam',
                'tinggi_badan' => 180,
                'berat_badan' => 74,
                'posisi_pemain' => 'Penjaga Gawang',
                'nomor_punggung' => 1,
            ],
            [
                'team_id' => 2,
                'nama_pemain' => 'Zalnando',
                'tinggi_badan' => 176,
                'berat_badan' => 71,
                'posisi_pemain' => 'Bertahan',
                'nomor_punggung' => 3,
            ],
            [
                'team_id' => 2,
                'nama_pemain' => 'Kakang Rudianto',
                'tinggi_badan' => 182,
                'berat_badan' => 76,
                'posisi_pemain' => 'Gelandang',
                'nomor_punggung' => 6,
            ],
            [
                'team_id' => 2,
                'nama_pemain' => 'Wander Luiz',
                'tinggi_badan' => 177,
                'berat_badan' => 73,
                'posisi_pemain' => 'Penyerang',
                'nomor_punggung' => 9,
            ],
            [
                'team_id' => 2,
                'nama_pemain' => 'Ciro Alves',
                'tinggi_badan' => 179,
                'berat_badan' => 75,
                'posisi_pemain' => 'Penyerang',
                'nomor_punggung' => 11,
            ],

            // Arema FC (team_id = 3)
            [
                'team_id' => 3,
                'nama_pemain' => 'Kurniawan Kartika Ajie',
                'tinggi_badan' => 185,
                'berat_badan' => 78,
                'posisi_pemain' => 'Penjaga Gawang',
                'nomor_punggung' => 1,
            ],
            [
                'team_id' => 3,
                'nama_pemain' => 'Bagas Adi Nugroho',
                'tinggi_badan' => 174,
                'berat_badan' => 70,
                'posisi_pemain' => 'Bertahan',
                'nomor_punggung' => 4,
            ],
            [
                'team_id' => 3,
                'nama_pemain' => 'Dendi Santoso',
                'tinggi_badan' => 172,
                'berat_badan' => 68,
                'posisi_pemain' => 'Gelandang',
                'nomor_punggung' => 7,
            ],
            [
                'team_id' => 3,
                'nama_pemain' => 'Carlos Fortes',
                'tinggi_badan' => 181,
                'berat_badan' => 77,
                'posisi_pemain' => 'Penyerang',
                'nomor_punggung' => 99,
            ],
        ];

        foreach ($players as $player) {
            // Cek apakah tim ada
            if (Team::find($player['team_id'])) {
                Player::create($player);
            }
        }
    }
}
