<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Team;
use App\Models\Player;
use App\Models\MatchGame;
use App\Models\MatchResult;
use App\Models\Goal;

$teams = Team::limit(4)->get();
$players = Player::limit(10)->get();
$matches = MatchGame::limit(5)->get();

if ($teams->count() < 2 || $players->count() < 4 || $matches->count() < 1) {
    echo "Not enough base data. Need at least 2 teams, 4 players, and 1 match.\n";
    exit;
}

echo "Creating sample match results and goals...\n";

foreach ($matches as $match) {
    $homeScore = rand(0, 4);
    $awayScore = rand(0, 4);
    
    $matchResult = MatchResult::create([
        'match_game_id' => $match->id,
        'skor_tim_tuan_rumah' => $homeScore,
        'skor_tim_tamu' => $awayScore,
        'hasil_pertandingan' => $homeScore > $awayScore ? 'menang' : 
                               ($homeScore < $awayScore ? 'kalah' : 'seri'),
        'statistik_pertandingan' => json_encode([
            'possession' => [
                'home' => rand(40, 60),
                'away' => rand(40, 60)
            ],
            'shots' => [
                'home' => rand(5, 15),
                'away' => rand(5, 15)
            ],
            'corners' => [
                'home' => rand(2, 8),
                'away' => rand(2, 8)
            ]
        ])
    ]);
    
    echo "Created match result for match ID {$match->id}\n";
    
    $homeTeamPlayers = Player::where('team_id', $match->tim_tuan_rumah_id)->limit(5)->get();
    for ($i = 0; $i < $homeScore; $i++) {
        if ($homeTeamPlayers->count() > 0) {
            $player = $homeTeamPlayers->random();
            Goal::create([
                'match_result_id' => $matchResult->id,
                'player_id' => $player->id,
                'team_id' => $match->tim_tuan_rumah_id,
                'menit_gol' => rand(1, 90),
                'jenis_gol' => collect(['Normal', 'Penalti', 'Own Goal', 'Free Kick'])->random()
            ]);
            echo "Created goal for player {$player->nama_lengkap}\n";
        }
    }
    
    $awayTeamPlayers = Player::where('team_id', $match->tim_tamu_id)->limit(5)->get();
    for ($i = 0; $i < $awayScore; $i++) {
        if ($awayTeamPlayers->count() > 0) {
            $player = $awayTeamPlayers->random();
            Goal::create([
                'match_result_id' => $matchResult->id,
                'player_id' => $player->id,
                'team_id' => $match->tim_tamu_id,
                'menit_gol' => rand(1, 90),
                'jenis_gol' => collect(['Normal', 'Penalti', 'Own Goal', 'Free Kick'])->random()
            ]);
            echo "Created goal for player {$player->nama_lengkap}\n";
        }
    }
}

echo "Sample data created successfully!\n";
echo "Match Results: " . MatchResult::count() . "\n";
echo "Goals: " . Goal::count() . "\n";
