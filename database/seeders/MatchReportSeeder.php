<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MatchResult;
use App\Models\Goal;
use App\Models\MatchGame;
use App\Models\Player;
use Carbon\Carbon;

class MatchReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample match results for comprehensive reports...');

        // Get some match games to create results for
        $matchGames = MatchGame::take(5)->get();

        if ($matchGames->isEmpty()) {
            $this->command->warn('No match games found. Please run MatchGameSeeder first.');
            return;
        }

        $sampleResults = [
            [
                'skor_tim_tuan_rumah' => 3,
                'skor_tim_tamu' => 1,
                'catatan_hasil' => 'Pertandingan menarik dengan 3 gol tim tuan rumah',
                'goals' => [
                    ['team_type' => 'home', 'minute' => 15, 'second' => 30, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 35, 'second' => 0, 'type' => 'Penalti'],
                    ['team_type' => 'home', 'minute' => 67, 'second' => 15, 'type' => 'Free Kick'],
                    ['team_type' => 'home', 'minute' => 89, 'second' => 45, 'type' => 'Normal']
                ]
            ],
            [
                'skor_tim_tuan_rumah' => 2,
                'skor_tim_tamu' => 2,
                'catatan_hasil' => 'Hasil imbang dengan permainan yang sengit',
                'goals' => [
                    ['team_type' => 'home', 'minute' => 23, 'second' => 12, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 45, 'second' => 30, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 67, 'second' => 20, 'type' => 'Normal'],
                    ['team_type' => 'home', 'minute' => 88, 'second' => 10, 'type' => 'Free Kick']
                ]
            ],
            [
                'skor_tim_tuan_rumah' => 1,
                'skor_tim_tamu' => 4,
                'catatan_hasil' => 'Dominasi tim tamu dengan serangan balik yang mematikan',
                'goals' => [
                    ['team_type' => 'home', 'minute' => 12, 'second' => 0, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 25, 'second' => 15, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 54, 'second' => 30, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 73, 'second' => 45, 'type' => 'Penalti'],
                    ['team_type' => 'away', 'minute' => 90, 'second' => 0, 'type' => 'Normal']
                ]
            ],
            [
                'skor_tim_tuan_rumah' => 0,
                'skor_tim_tamu' => 0,
                'catatan_hasil' => 'Pertandingan tanpa gol dengan banyak peluang tersia-sia',
                'goals' => []
            ],
            [
                'skor_tim_tuan_rumah' => 5,
                'skor_tim_tamu' => 2,
                'catatan_hasil' => 'Pesta gol tim tuan rumah dengan 7 gol dalam satu pertandingan',
                'goals' => [
                    ['team_type' => 'home', 'minute' => 8, 'second' => 20, 'type' => 'Normal'],
                    ['team_type' => 'home', 'minute' => 19, 'second' => 45, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 33, 'second' => 10, 'type' => 'Free Kick'],
                    ['team_type' => 'home', 'minute' => 56, 'second' => 30, 'type' => 'Penalti'],
                    ['team_type' => 'home', 'minute' => 71, 'second' => 15, 'type' => 'Normal'],
                    ['team_type' => 'away', 'minute' => 84, 'second' => 0, 'type' => 'Normal'],
                    ['team_type' => 'home', 'minute' => 90, 'second' => 30, 'type' => 'Normal']
                ]
            ]
        ];

        foreach ($matchGames->take(5) as $index => $match) {
            if (!isset($sampleResults[$index])) continue;

            $resultData = $sampleResults[$index];

            // Update match status to completed
            $match->update(['status_pertandingan' => 'Selesai']);

            // Create match result
            $matchResult = MatchResult::create([
                'match_game_id' => $match->id,
                'skor_tim_tuan_rumah' => $resultData['skor_tim_tuan_rumah'],
                'skor_tim_tamu' => $resultData['skor_tim_tamu'],
                'catatan_hasil' => $resultData['catatan_hasil'],
                'waktu_laporan' => Carbon::now()->subHours(rand(1, 48))
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

            $this->command->info("Created result for match: {$match->tim_tuan_rumah_id} vs {$match->tim_tamu_id} ({$resultData['skor_tim_tuan_rumah']}-{$resultData['skor_tim_tamu']})");
        }

        $this->command->info('Sample match results for comprehensive reports created successfully!');
    }
}
