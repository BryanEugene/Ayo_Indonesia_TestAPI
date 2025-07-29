<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchResult;
use App\Models\MatchGame;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MatchReportController extends Controller
{
    /**
     * Get comprehensive match reports
     */
    public function getMatchReports(Request $request): JsonResponse
    {
        try {
            $query = MatchResult::with([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ]);

            // Filter by date range
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

            // Filter by team
            if ($request->has('team_id')) {
                $teamId = $request->get('team_id');
                $query->whereHas('matchGame', function ($q) use ($teamId) {
                    $q->where('tim_tuan_rumah_id', $teamId)
                      ->orWhere('tim_tamu_id', $teamId);
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
                        'formatted_datetime' => $match->tanggal_pertandingan->format('d/m/Y') . ' ' . $match->waktu_pertandingan->format('H:i')
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
                        'display' => $result->skor_tim_tuan_rumah . ' - ' . $result->skor_tim_tamu
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
                'total_matches' => count($reports)
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
     * Get match report by ID
     */
    public function getMatchReportById(int $matchResultId): JsonResponse
    {
        try {
            $result = MatchResult::with([
                'matchGame.timTuanRumah', 
                'matchGame.timTamu',
                'goals.player',
                'goals.team'
            ])->find($matchResultId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hasil pertandingan tidak ditemukan',
                    'data' => null
                ], 404);
            }

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

            $report = [
                'match_result_id' => $result->id,
                'match_game_id' => $match->id,
                'jadwal_pertandingan' => [
                    'tanggal' => $match->tanggal_pertandingan->format('d/m/Y'),
                    'waktu' => $match->waktu_pertandingan->format('H:i'),
                    'tempat' => $match->tempat_pertandingan,
                    'formatted_datetime' => $match->tanggal_pertandingan->format('d/m/Y') . ' ' . $match->waktu_pertandingan->format('H:i')
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
                    'display' => $result->skor_tim_tuan_rumah . ' - ' . $result->skor_tim_tamu
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

            return response()->json([
                'success' => true,
                'message' => 'Laporan hasil pertandingan berhasil diambil',
                'data' => $report
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
     * Get top goalscorer for a specific match
     */
    private function getTopGoalscorerForMatch(int $matchResultId)
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
     * Get team statistics up to a specific date
     */
    private function getTeamStatsUpToDate(int $teamId, $upToDate)
    {
        // Get all completed matches for this team up to the specified date
        $matches = MatchResult::whereHas('matchGame', function ($query) use ($teamId, $upToDate) {
            $query->where(function ($q) use ($teamId) {
                $q->where('tim_tuan_rumah_id', $teamId)
                  ->orWhere('tim_tamu_id', $teamId);
            })
            ->where('tanggal_pertandingan', '<=', $upToDate)
            ->where('status_pertandingan', 'Selesai');
        })->with('matchGame')->get();

        $wins = 0;
        $losses = 0;
        $draws = 0;

        foreach ($matches as $result) {
            $match = $result->matchGame;
            $isHomeTeam = $match->tim_tuan_rumah_id == $teamId;
            
            if ($isHomeTeam) {
                // Team is playing at home
                if ($result->skor_tim_tuan_rumah > $result->skor_tim_tamu) {
                    $wins++;
                } elseif ($result->skor_tim_tuan_rumah < $result->skor_tim_tamu) {
                    $losses++;
                } else {
                    $draws++;
                }
            } else {
                // Team is playing away
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

    /**
     * Get team statistics summary
     */
    public function getTeamStatistics(Request $request): JsonResponse
    {
        try {
            $teamId = $request->get('team_id');
            $upToDate = $request->get('up_to_date', Carbon::now()->toDateString());

            if (!$teamId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID tim wajib diisi',
                    'data' => null
                ], 400);
            }

            $stats = $this->getTeamStatsUpToDate($teamId, $upToDate);

            return response()->json([
                'success' => true,
                'message' => 'Statistik tim berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
