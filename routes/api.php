<?php

use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\MatchGameController;
use App\Http\Controllers\Api\MatchResultController;
use App\Http\Controllers\Api\MatchReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {
    // Team management routes - specific routes first
    Route::get('teams/trashed', [TeamController::class, 'trashed']);
    Route::post('teams/{id}/restore', [TeamController::class, 'restore']);
    Route::delete('teams/{id}/force-delete', [TeamController::class, 'forceDelete']);
    Route::get('teams/city/{city}', [TeamController::class, 'getByCity']);
    Route::apiResource('teams', TeamController::class);
    
    // Player management routes - specific routes first
    Route::get('players/trashed', [PlayerController::class, 'trashed']);
    Route::post('players/{id}/restore', [PlayerController::class, 'restore']);
    Route::delete('players/{id}/force-delete', [PlayerController::class, 'forceDelete']);
    Route::get('players/position/{position}', [PlayerController::class, 'getByPosition']);
    Route::get('teams/{team}/players', [PlayerController::class, 'getByTeam']);
    Route::apiResource('players', PlayerController::class);
    
    // Match management routes - specific routes first
    Route::get('matches/trashed', [MatchGameController::class, 'trashed']);
    Route::post('matches/{id}/restore', [MatchGameController::class, 'restore']);
    Route::delete('matches/{id}/force-delete', [MatchGameController::class, 'forceDelete']);
    Route::get('matches/upcoming', [MatchGameController::class, 'upcoming']);
    Route::get('matches/today', [MatchGameController::class, 'today']);
    Route::get('teams/{team}/matches', [MatchGameController::class, 'getByTeam']);
    Route::apiResource('matches', MatchGameController::class, ['parameters' => ['matches' => 'matchGame']]);
    
    // Match result management routes - specific routes first
    Route::get('match-results/trashed', [MatchResultController::class, 'trashed']);
    Route::post('match-results/{id}/restore', [MatchResultController::class, 'restore']);
    Route::delete('match-results/{id}/force-delete', [MatchResultController::class, 'forceDelete']);
    Route::get('matches/{matchGame}/result', [MatchResultController::class, 'getByMatch']);
    Route::get('players/{playerId}/goals', [MatchResultController::class, 'getGoalsByPlayer']);
    Route::get('match-reports', [MatchResultController::class, 'getMatchReport']);
    Route::apiResource('match-results', MatchResultController::class, ['parameters' => ['match-results' => 'matchResult']]);
    
    // Match report routes - Comprehensive match reports
    Route::get('match-reports/comprehensive', [MatchReportController::class, 'getMatchReports']);
    Route::get('match-reports/{matchResultId}', [MatchReportController::class, 'getMatchReportById']);
    Route::get('team-statistics', [MatchReportController::class, 'getTeamStatistics']);
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toISOString()
    ]);
});
