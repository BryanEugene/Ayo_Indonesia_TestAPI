<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FootballMatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua tim yang ada
        $teams = Team::all();

        if ($teams->count() < 2) {
            $this->command->warn('Minimal 2 tim diperlukan untuk membuat jadwal pertandingan.');
            return;
        }

        $matches = [
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(3)->format('Y-m-d'),
                'waktu_pertandingan' => '15:30',
                'tim_tuan_rumah' => 1, // Persija Jakarta
                'tim_tamu' => 2, // Persib Bandung
                'tempat_pertandingan' => 'Stadion Gelora Bung Karno',
                'status_pertandingan' => 'Terjadwal',
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(5)->format('Y-m-d'),
                'waktu_pertandingan' => '19:00',
                'tim_tuan_rumah' => 3, // Arema FC
                'tim_tamu' => 1, // Persija Jakarta
                'tempat_pertandingan' => 'Stadion Kanjuruhan',
                'status_pertandingan' => 'Terjadwal',
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(7)->format('Y-m-d'),
                'waktu_pertandingan' => '16:00',
                'tim_tuan_rumah' => 2, // Persib Bandung
                'tim_tamu' => 4, // Bali United
                'tempat_pertandingan' => 'Stadion Gelora Bandung Lautan Api',
                'status_pertandingan' => 'Terjadwal',
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(10)->format('Y-m-d'),
                'waktu_pertandingan' => '20:00',
                'tim_tuan_rumah' => 5, // PSM Makassar
                'tim_tamu' => 3, // Arema FC
                'tempat_pertandingan' => 'Stadion Gelora BJ Habibie',
                'status_pertandingan' => 'Terjadwal',
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->addDays(12)->format('Y-m-d'),
                'waktu_pertandingan' => '15:00',
                'tim_tuan_rumah' => 4, // Bali United
                'tim_tamu' => 5, // PSM Makassar
                'tempat_pertandingan' => 'Stadion Kapten I Wayan Dipta',
                'status_pertandingan' => 'Terjadwal',
            ],
            // Beberapa pertandingan yang sudah selesai
            [
                'tanggal_pertandingan' => Carbon::today()->subDays(7)->format('Y-m-d'),
                'waktu_pertandingan' => '15:30',
                'tim_tuan_rumah' => 1, // Persija Jakarta
                'tim_tamu' => 3, // Arema FC
                'tempat_pertandingan' => 'Stadion Gelora Bung Karno',
                'status_pertandingan' => 'Selesai',
            ],
            [
                'tanggal_pertandingan' => Carbon::today()->subDays(3)->format('Y-m-d'),
                'waktu_pertandingan' => '19:00',
                'tim_tuan_rumah' => 2, // Persib Bandung
                'tim_tamu' => 5, // PSM Makassar
                'tempat_pertandingan' => 'Stadion Gelora Bandung Lautan Api',
                'status_pertandingan' => 'Selesai',
            ],
        ];

        foreach ($matches as $match) {
            // Cek apakah tim ada sebelum membuat pertandingan
            if (Team::find($match['tim_tuan_rumah']) && Team::find($match['tim_tamu'])) {
                FootballMatch::create($match);
            }
        }

        $this->command->info('Sample football matches created successfully!');
    }
}
