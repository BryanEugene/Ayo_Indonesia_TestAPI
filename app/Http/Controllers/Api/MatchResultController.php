<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatchResultRequest;
use App\Models\MatchResult;
use App\Models\MatchGame;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchResultController extends Controller
{
    /**
     * Display a listing of match results.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MatchResult::with([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ]);

            // Filter by match game
            if ($request->has('match_game_id')) {
                $query->where('match_game_id', $request->get('match_game_id'));
            }

            // Filter by team (either home or away)
            if ($request->has('team_id')) {
                $teamId = $request->get('team_id');
                $query->whereHas('matchGame', function ($q) use ($teamId) {
                    $q->where('tim_tuan_rumah_id', $teamId)
                      ->orWhere('tim_tamu_id', $teamId);
                });
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->whereHas('matchGame', function ($q) use ($search) {
                    $q->whereHas('timTuanRumah', function ($teamQuery) use ($search) {
                        $teamQuery->where('nama_tim', 'like', "%{$search}%");
                    })->orWhereHas('timTamu', function ($teamQuery) use ($search) {
                        $teamQuery->where('nama_tim', 'like', "%{$search}%");
                    });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $results = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data hasil pertandingan berhasil diambil',
                'data' => $results->items(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'from' => $results->firstItem(),
                    'to' => $results->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hasil pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created match result in storage.
     */
    public function store(MatchResultRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Create match result
            $matchResult = MatchResult::create([
                'match_game_id' => $request->match_game_id,
                'skor_tim_tuan_rumah' => $request->skor_tim_tuan_rumah,
                'skor_tim_tamu' => $request->skor_tim_tamu,
                'catatan_hasil' => $request->catatan_hasil,
                'waktu_laporan' => $request->waktu_laporan ?? now(),
            ]);

            // Create goals if provided
            if ($request->has('goals') && is_array($request->goals)) {
                foreach ($request->goals as $goalData) {
                    Goal::create([
                        'match_result_id' => $matchResult->id,
                        'player_id' => $goalData['player_id'],
                        'team_id' => $goalData['team_id'],
                        'menit_gol' => $goalData['menit_gol'],
                        'detik_gol' => $goalData['detik_gol'] ?? 0,
                        'jenis_gol' => $goalData['jenis_gol'] ?? 'Normal',
                        'deskripsi_gol' => $goalData['deskripsi_gol'] ?? null,
                    ]);
                }
            }

            // Update match status to completed
            $match = MatchGame::find($request->match_game_id);
            if ($match && $match->status_pertandingan !== 'Selesai') {
                $match->update(['status_pertandingan' => 'Selesai']);
            }

            // Load relationships for response
            $matchResult->load([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hasil pertandingan berhasil dilaporkan',
                'data' => $matchResult
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melaporkan hasil pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified match result.
     */
    public function show(MatchResult $matchResult): JsonResponse
    {
        try {
            $matchResult->load([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goalsOrderedByTime.player',
                'goalsOrderedByTime.team'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Detail hasil pertandingan berhasil diambil',
                'data' => $matchResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hasil pertandingan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified match result in storage.
     */
    public function update(MatchResultRequest $request, MatchResult $matchResult): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Update match result
            $matchResult->update([
                'skor_tim_tuan_rumah' => $request->skor_tim_tuan_rumah,
                'skor_tim_tamu' => $request->skor_tim_tamu,
                'catatan_hasil' => $request->catatan_hasil,
                'waktu_laporan' => $request->waktu_laporan ?? $matchResult->waktu_laporan,
            ]);

            // Delete existing goals
            $matchResult->goals()->delete();

            // Create new goals if provided
            if ($request->has('goals') && is_array($request->goals)) {
                foreach ($request->goals as $goalData) {
                    Goal::create([
                        'match_result_id' => $matchResult->id,
                        'player_id' => $goalData['player_id'],
                        'team_id' => $goalData['team_id'],
                        'menit_gol' => $goalData['menit_gol'],
                        'detik_gol' => $goalData['detik_gol'] ?? 0,
                        'jenis_gol' => $goalData['jenis_gol'] ?? 'Normal',
                        'deskripsi_gol' => $goalData['deskripsi_gol'] ?? null,
                    ]);
                }
            }

            // Load relationships for response
            $matchResult->load([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hasil pertandingan berhasil diperbarui',
                'data' => $matchResult
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui hasil pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified match result from storage (soft delete).
     */
    public function destroy(MatchResult $matchResult): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $matchDescription = $matchResult->matchGame->match_description;
            
            // Reset match status back to scheduled if needed
            $match = $matchResult->matchGame;
            if ($match) {
                $match->update(['status_pertandingan' => 'Dijadwalkan']);
            }
            
            // Soft delete match result
            $matchResult->delete(); // This will soft delete

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Hasil pertandingan {$matchDescription} berhasil dihapus (soft delete)"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus hasil pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all trashed match results.
     */
    public function trashed(): JsonResponse
    {
        try {
            $trashedResults = MatchResult::onlyTrashed()->with([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data hasil pertandingan yang terhapus berhasil diambil',
                'data' => $trashedResults,
                'total' => $trashedResults->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hasil pertandingan yang terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a trashed match result.
     */
    public function restore(int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $matchResult = MatchResult::onlyTrashed()->findOrFail($id);
            $matchResult->restore();
            
            // Update match status back to completed
            $match = $matchResult->matchGame;
            if ($match) {
                $match->update(['status_pertandingan' => 'Selesai']);
            }
            
            $matchResult->load([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Hasil pertandingan berhasil dipulihkan",
                'data' => $matchResult
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan hasil pertandingan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Permanently delete a match result.
     */
    public function forceDelete(int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $matchResult = MatchResult::onlyTrashed()->findOrFail($id);
            $matchDescription = $matchResult->matchGame->match_description;
            $matchResult->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Hasil pertandingan {$matchDescription} berhasil dihapus permanen"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus hasil pertandingan secara permanen',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get match result by match game ID.
     */
    public function getByMatch(MatchGame $matchGame): JsonResponse
    {
        try {
            $matchResult = $matchGame->matchResult;
            
            if (!$matchResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pertandingan belum memiliki hasil',
                    'data' => null
                ], 404);
            }

            $matchResult->load([
                'goalsOrderedByTime.player',
                'goalsOrderedByTime.team'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hasil pertandingan berhasil diambil',
                'data' => [
                    'match' => $matchGame->load(['timTuanRumah', 'timTamu']),
                    'result' => $matchResult
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil hasil pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get goals by player.
     */
    public function getGoalsByPlayer(Request $request, int $playerId): JsonResponse
    {
        try {
            $query = Goal::with(['matchResult.matchGame.timTuanRumah', 'matchResult.matchGame.timTamu', 'team'])
                        ->where('player_id', $playerId);

            // Filter by match if provided
            if ($request->has('match_game_id')) {
                $query->whereHas('matchResult', function ($q) use ($request) {
                    $q->where('match_game_id', $request->get('match_game_id'));
                });
            }

            $goals = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Gol pemain berhasil diambil',
                'data' => $goals,
                'total_goals' => $goals->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gol pemain',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive match report with statistics.
     */
    public function getMatchReport(Request $request): JsonResponse
    {
        try {
            $query = MatchResult::with([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ]);

            // Apply filters
            if ($request->has('match_game_id')) {
                $query->where('match_game_id', $request->get('match_game_id'));
            }

            if ($request->has('team_id')) {
                $teamId = $request->get('team_id');
                $query->whereHas('matchGame', function ($q) use ($teamId) {
                    $q->where('tim_tuan_rumah_id', $teamId)
                      ->orWhere('tim_tamu_id', $teamId);
                });
            }

            if ($request->has('date_from')) {
                $query->whereHas('matchGame', function ($q) use ($request) {
                    $q->where('tanggal_pertandingan', '>=', $request->get('date_from'));
                });
            }

            if ($request->has('date_to')) {
                $query->whereHas('matchGame', function ($q) use ($request) {
                    $q->where('tanggal_pertandingan', '<=', $request->get('date_to'));
                });
            }

            $matchResults = $query->orderBy('created_at', 'desc')->get();

            $reports = [];

            foreach ($matchResults as $result) {
                $match = $result->matchGame;
                $homeTeam = $match->timTuanRumah;
                $awayTeam = $match->timTamu;

                // Determine match status
                $matchStatus = 'Draw';
                if ($result->skor_tim_tuan_rumah > $result->skor_tim_tamu) {
                    $matchStatus = 'Tim Home Menang';
                } elseif ($result->skor_tim_tamu > $result->skor_tim_tuan_rumah) {
                    $matchStatus = 'Tim Away Menang';
                }

                // Find top goalscorer for this match
                $topGoalscorer = $this->getTopGoalscorerForMatch($result->id);

                // Calculate team statistics up to this match date
                $homeTeamStats = $this->getTeamStatsUpToDate($homeTeam->id, $match->tanggal_pertandingan);
                $awayTeamStats = $this->getTeamStatsUpToDate($awayTeam->id, $match->tanggal_pertandingan);

                $reports[] = [
                    'match_result_id' => $result->id,
                    'match_game_id' => $match->id,
                    'jadwal_pertandingan' => [
                        'tanggal' => $match->tanggal_pertandingan->format('d/m/Y'),
                        'waktu' => $match->waktu_pertandingan->format('H:i'),
                        'tempat' => $match->tempat_pertandingan,
                        'formatted_datetime' => $match->formatted_date_time
                    ],
                    'tim_home' => [
                        'id' => $homeTeam->id,
                        'nama' => $homeTeam->nama_tim,
                        'logo' => $homeTeam->logo_tim,
                        'kota' => $homeTeam->kota_markas
                    ],
                    'tim_away' => [
                        'id' => $awayTeam->id,
                        'nama' => $awayTeam->nama_tim,
                        'logo' => $awayTeam->logo_tim,
                        'kota' => $awayTeam->kota_markas
                    ],
                    'skor_akhir' => [
                        'home' => $result->skor_tim_tuan_rumah,
                        'away' => $result->skor_tim_tamu,
                        'display' => $result->result_summary
                    ],
                    'status_akhir_pertandingan' => $matchStatus,
                    'pemain_pencetak_gol_terbanyak' => $topGoalscorer,
                    'akumulasi_kemenangan_tim_home' => [
                        'total_menang' => $homeTeamStats['wins'],
                        'total_kalah' => $homeTeamStats['losses'],
                        'total_draw' => $homeTeamStats['draws'],
                        'total_pertandingan' => $homeTeamStats['total_matches'],
                        'persentase_kemenangan' => $homeTeamStats['win_percentage'] . '%'
                    ],
                    'akumulasi_kemenangan_tim_away' => [
                        'total_menang' => $awayTeamStats['wins'],
                        'total_kalah' => $awayTeamStats['losses'],
                        'total_draw' => $awayTeamStats['draws'],
                        'total_pertandingan' => $awayTeamStats['total_matches'],
                        'persentase_kemenangan' => $awayTeamStats['win_percentage'] . '%'
                    ],
                    'detail_gol' => $result->goals->map(function ($goal) {
                        return [
                            'pemain' => $goal->player->nama_pemain,
                            'tim' => $goal->team->nama_tim,
                            'waktu' => $goal->formatted_time,
                            'jenis' => $goal->jenis_gol,
                            'deskripsi' => $goal->deskripsi_gol
                        ];
                    }),
                    'catatan_hasil' => $result->catatan_hasil,
                    'waktu_laporan' => $result->waktu_laporan ? $result->waktu_laporan->format('d/m/Y H:i:s') : null
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan hasil pertandingan berhasil diambil',
                'data' => $reports,
                'total_matches' => count($reports),
                'filters_applied' => [
                    'match_game_id' => $request->get('match_game_id'),
                    'team_id' => $request->get('team_id'),
                    'date_from' => $request->get('date_from'),
                    'date_to' => $request->get('date_to')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan hasil pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top goalscorer for a specific match.
     */
    private function getTopGoalscorerForMatch(int $matchResultId): ?array
    {
        $topGoalscorer = Goal::select('player_id', DB::raw('COUNT(*) as goal_count'))
            ->with('player', 'team')
            ->where('match_result_id', $matchResultId)
            ->groupBy('player_id')
            ->orderBy('goal_count', 'desc')
            ->first();

        if (!$topGoalscorer) {
            return null;
        }

        return [
            'pemain_id' => $topGoalscorer->player->id,
            'nama_pemain' => $topGoalscorer->player->nama_pemain,
            'tim' => $topGoalscorer->team->nama_tim,
            'jumlah_gol' => $topGoalscorer->goal_count,
            'posisi' => $topGoalscorer->player->posisi_pemain,
            'nomor_punggung' => $topGoalscorer->player->nomor_punggung
        ];
    }

    /**
     * Get team statistics up to a specific date.
     */
    private function getTeamStatsUpToDate(int $teamId, $upToDate): array
    {
        // Get all matches for this team up to the specified date
        $matches = MatchResult::whereHas('matchGame', function ($query) use ($teamId, $upToDate) {
            $query->where(function ($q) use ($teamId) {
                $q->where('tim_tuan_rumah_id', $teamId)
                  ->orWhere('tim_tamu_id', $teamId);
            })->where('tanggal_pertandingan', '<=', $upToDate);
        })->with('matchGame')->get();

        $wins = 0;
        $losses = 0;
        $draws = 0;

        foreach ($matches as $result) {
            $match = $result->matchGame;
            $isHomeTeam = $match->tim_tuan_rumah_id == $teamId;

            if ($isHomeTeam) {
                if ($result->skor_tim_tuan_rumah > $result->skor_tim_tamu) {
                    $wins++;
                } elseif ($result->skor_tim_tuan_rumah < $result->skor_tim_tamu) {
                    $losses++;
                } else {
                    $draws++;
                }
            } else {
                if ($result->skor_tim_tamu > $result->skor_tim_tuan_rumah) {
                    $wins++;
                } elseif ($result->skor_tim_tamu < $result->skor_tim_tuan_rumah) {
                    $losses++;
                } else {
                    $draws++;
                }
            }
        }

        $totalMatches = $wins + $losses + $draws;
        $winPercentage = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;

        return [
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'total_matches' => $totalMatches,
            'win_percentage' => $winPercentage
        ];
    }
}
