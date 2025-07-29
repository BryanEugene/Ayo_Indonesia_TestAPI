<?php

namespace Database\Seeders;

use App\Models\MatchGame;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatchGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua tim yang ada
        $teams = Team::all();

        if ($teams->count() < 2) {
            $this->command->warn('Perlu minimal 2 tim untuk membuat jadwal pertandingan. Jalankan TeamSeeder terlebih dahulu.');
            return;
        }

        $matchGames = [
            // Pertandingan minggu ini
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(2),
                'waktu_pertandingan' => '15:30',
                'tim_tuan_rumah_id' => 1, // Persija Jakarta
                'tim_tamu_id' => 2,       // Persib Bandung
                'tempat_pertandingan' => 'Stadion Gelora Bung Karno',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => 'Pertandingan klasik Jakarta vs Bandung'
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(3),
                'waktu_pertandingan' => '19:00',
                'tim_tuan_rumah_id' => 3, // Arema FC
                'tim_tamu_id' => 4,       // Bali United
                'tempat_pertandingan' => 'Stadion Kanjuruhan',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => 'Pertandingan malam minggu'
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(5),
                'waktu_pertandingan' => '16:00',
                'tim_tuan_rumah_id' => 5, // PSM Makassar
                'tim_tamu_id' => 1,       // Persija Jakarta
                'tempat_pertandingan' => 'Stadion Gelora BJ Habibie',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => null
            ],
            
            // Pertandingan minggu depan
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(8),
                'waktu_pertandingan' => '15:00',
                'tim_tuan_rumah_id' => 2, // Persib Bandung
                'tim_tamu_id' => 3,       // Arema FC
                'tempat_pertandingan' => 'Stadion Gelora Bandung Lautan Api',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => 'Derby Jawa Timur vs Jawa Barat'
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(10),
                'waktu_pertandingan' => '20:00',
                'tim_tuan_rumah_id' => 4, // Bali United
                'tim_tamu_id' => 5,       // PSM Makassar
                'tempat_pertandingan' => 'Stadion Kapten I Wayan Dipta',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => 'Pertandingan malam di Bali'
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(12),
                'waktu_pertandingan' => '14:00',
                'tim_tuan_rumah_id' => 1, // Persija Jakarta
                'tim_tamu_id' => 4,       // Bali United
                'tempat_pertandingan' => 'Stadion Gelora Bung Karno',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => 'Pertandingan siang'
            ],

            // Pertandingan yang sudah selesai (untuk testing)
            [
                'tanggal_pertandingan' => Carbon::today()->subDays(7),
                'waktu_pertandingan' => '16:30',
                'tim_tuan_rumah_id' => 2, // Persib Bandung
                'tim_tamu_id' => 1,       // Persija Jakarta
                'tempat_pertandingan' => 'Stadion Gelora Bandung Lautan Api',
                'status_pertandingan' => 'Selesai',
                'catatan' => 'Pertandingan minggu lalu'
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->subDays(5),
                'waktu_pertandingan' => '19:30',
                'tim_tuan_rumah_id' => 3, // Arema FC
                'tim_tamu_id' => 5,       // PSM Makassar
                'tempat_pertandingan' => 'Stadion Kanjuruhan',
                'status_pertandingan' => 'Selesai',
                'catatan' => 'Pertandingan sudah selesai'
            ],

            // Pertandingan hari ini (untuk testing)
            [
                'tanggal_pertandingan' => Carbon::today(),
                'waktu_pertandingan' => '20:30',
                'tim_tuan_rumah_id' => 4, // Bali United
                'tim_tamu_id' => 2,       // Persib Bandung
                'tempat_pertandingan' => 'Stadion Kapten I Wayan Dipta',
                'status_pertandingan' => 'Dijadwalkan',
                'catatan' => 'Pertandingan malam ini'
            ]
        ];

        foreach ($matchGames as $matchGame) {
            // Cek apakah kedua tim ada
            if (Team::find($matchGame['tim_tuan_rumah_id']) && Team::find($matchGame['tim_tamu_id'])) {
                MatchGame::create($matchGame);
            }
        }

        $this->command->info('Sample match games created successfully!');
    }
}
