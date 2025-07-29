<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MatchResult;
use App\Models\Goal;
use App\Models\MatchGame;
use App\Models\Player;
use Carbon\Carbon;

class MatchResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample match results and goals...');

        // Get finished matches (we'll use the past matches from our seeder)
        $finishedMatches = MatchGame::where('status_pertandingan', 'Selesai')->get();

        if ($finishedMatches->isEmpty()) {
            // Update some matches to be finished
            $pastMatches = MatchGame::where('tanggal_pertandingan', '<', Carbon::today())->take(3)->get();
            
            foreach ($pastMatches as $match) {
                $match->update(['status_pertandingan' => 'Selesai']);
            }
            
            $finishedMatches = $pastMatches;
        }

        $sampleResults = [
            [
                'skor_tim_tuan_rumah' => 2,
                'skor_tim_tamu' => 1,
                'catatan_hasil' => 'Pertandingan sengit dengan 2 kartu kuning',
                'goals' => [
                    ['team_type' => 'home', 'minute' => 15, 'second' => 30, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 45, 'second' => 0, 'type' => 'Penalti'],
                    ['team_type' => 'home', 'minute' => 78, 'second' => 15, 'type' => 'Sundulan']
                ]
            ],
            [
                'skor_tim_tuan_rumah' => 0,
                'skor_tim_tamu' => 3,
                'catatan_hasil' => 'Dominasi tim tamu sepanjang pertandingan',
                'goals' => [
                    ['team_type' => 'away', 'minute' => 23, 'second' => 45, 'type' => 'Tendangan Bebas'],
                    ['team_type' => 'away', 'minute' => 56, 'second' => 12, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 89, 'second' => 30, 'type' => 'Counter Attack']
                ]
            ],
            [
                'skor_tim_tuan_rumah' => 1,
                'skor_tim_tamu' => 1,
                'catatan_hasil' => 'Pertandingan berimbang, kedua tim bermain defensif',
                'goals' => [
                    ['team_type' => 'home', 'minute' => 34, 'second' => 20, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 67, 'second' => 5, 'type' => 'Rebound']
                ]
            ]
        ];

        foreach ($finishedMatches->take(3) as $index => $match) {
            if (!isset($sampleResults[$index])) continue;

            $resultData = $sampleResults[$index];

            // Create match result
            $matchResult = MatchResult::create([
                'match_game_id' => $match->id,
                'skor_tim_tuan_rumah' => $resultData['skor_tim_tuan_rumah'],
                'skor_tim_tamu' => $resultData['skor_tim_tamu'],
                'catatan_hasil' => $resultData['catatan_hasil'],
                'waktu_laporan' => Carbon::now()->subHours(rand(1, 24))
            ]);

            // Get players from both teams
            $homePlayers = Player::where('team_id', $match->tim_tuan_rumah_id)->get();
            $awayPlayers = Player::where('team_id', $match->tim_tamu_id)->get();

            // Create goals
            foreach ($resultData['goals'] as $goalData) {
                if ($goalData['team_type'] === 'home' && $homePlayers->isNotEmpty()) {
                    $player = $homePlayers->random();
                    $teamId = $match->tim_tuan_rumah_id;
                } elseif ($goalData['team_type'] === 'away' && $awayPlayers->isNotEmpty()) {
                    $player = $awayPlayers->random();
                    $teamId = $match->tim_tamu_id;
                } else {
                    continue; // Skip if no players available
                }

                Goal::create([
                    'match_result_id' => $matchResult->id,
                    'player_id' => $player->id,
                    'team_id' => $teamId,
                    'menit_gol' => $goalData['minute'],
                    'detik_gol' => $goalData['second'],
                    'jenis_gol' => $goalData['type'],
                    'deskripsi_gol' => "Gol {$goalData['type']} oleh {$player->nama_pemain} pada menit ke-{$goalData['minute']}"
                ]);
            }

            $this->command->info("Created result for match: {$match->match_description} ({$resultData['skor_tim_tuan_rumah']}-{$resultData['skor_tim_tamu']})");
        }

        $this->command->info('Sample match results and goals created successfully!');
    }
}
