<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/', function () {
    $teams = \App\Models\Team::with('players')->get();
    return view('home', compact('teams'));
})->name('home');

Route::get('/database', function () {
    // Ambil semua data dari database
    $teams = \App\Models\Team::with('players')->get();
    $players = \App\Models\Player::with('team')->get();
    $matches = \App\Models\MatchGame::with(['timTuanRumah', 'timTamu'])->get();
    $matchResults = \App\Models\MatchResult::with(['matchGame.timTuanRumah', 'matchGame.timTamu', 'goals.player', 'goals.team'])->get();
    $goals = \App\Models\Goal::with(['player', 'team', 'matchResult'])->get();
    
    return view('database', compact('teams', 'players', 'matches', 'matchResults', 'goals'));
})->name('database');

// Route::get('/wellcome', function () {
//     return view('wellcome');
// });