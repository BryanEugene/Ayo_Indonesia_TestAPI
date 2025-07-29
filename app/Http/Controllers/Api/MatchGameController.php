<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatchGameRequest;
use App\Models\MatchGame;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MatchGameController extends Controller
{
    /**
     * Display a listing of match games.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MatchGame::with(['timTuanRumah', 'timTamu']);

            // Filter by date
            if ($request->has('tanggal')) {
                $query->whereDate('tanggal_pertandingan', $request->get('tanggal'));
            }

            // Filter by team (either home or away)
            if ($request->has('team_id')) {
                $teamId = $request->get('team_id');
                $query->where(function ($q) use ($teamId) {
                    $q->where('tim_tuan_rumah_id', $teamId)
                      ->orWhere('tim_tamu_id', $teamId);
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status_pertandingan', $request->get('status'));
            }

            // Filter upcoming matches (default)
            if ($request->get('filter') === 'upcoming') {
                $query->upcoming();
            } elseif ($request->get('filter') === 'today') {
                $query->today();
            } elseif ($request->get('filter') === 'past') {
                $query->where('tanggal_pertandingan', '<', Carbon::today())
                      ->orderBy('tanggal_pertandingan', 'desc')
                      ->orderBy('waktu_pertandingan', 'desc');
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->whereHas('timTuanRumah', function ($teamQuery) use ($search) {
                        $teamQuery->where('nama_tim', 'like', "%{$search}%");
                    })->orWhereHas('timTamu', function ($teamQuery) use ($search) {
                        $teamQuery->where('nama_tim', 'like', "%{$search}%");
                    })->orWhere('tempat_pertandingan', 'like', "%{$search}%");
                });
            }

            // Default sorting
            if (!$request->has('filter') || $request->get('filter') !== 'past') {
                $query->orderBy('tanggal_pertandingan')
                      ->orderBy('waktu_pertandingan');
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $matchGames = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal pertandingan berhasil diambil',
                'data' => $matchGames->items(),
                'pagination' => [
                    'current_page' => $matchGames->currentPage(),
                    'last_page' => $matchGames->lastPage(),
                    'per_page' => $matchGames->perPage(),
                    'total' => $matchGames->total(),
                    'from' => $matchGames->firstItem(),
                    'to' => $matchGames->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jadwal pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created match game in storage.
     */
    public function store(MatchGameRequest $request): JsonResponse
    {
        try {
            $matchGame = MatchGame::create($request->validated());
            $matchGame->load(['timTuanRumah', 'timTamu']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pertandingan berhasil ditambahkan',
                'data' => $matchGame
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jadwal pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified match game.
     */
    public function show(MatchGame $matchGame): JsonResponse
    {
        try {
            $matchGame->load(['timTuanRumah', 'timTamu']);
            
            return response()->json([
                'success' => true,
                'message' => 'Detail jadwal pertandingan berhasil diambil',
                'data' => $matchGame
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal pertandingan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified match game in storage.
     */
    public function update(MatchGameRequest $request, MatchGame $matchGame): JsonResponse
    {
        try {
            $matchGame->update($request->validated());
            $matchGame->load(['timTuanRumah', 'timTamu']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pertandingan berhasil diperbarui',
                'data' => $matchGame
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jadwal pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified match game from storage (soft delete).
     */
    public function destroy(MatchGame $matchGame): JsonResponse
    {
        try {
            $matchDescription = $matchGame->match_description;
            $matchGame->delete(); // This will soft delete

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan {$matchDescription} berhasil dihapus (soft delete)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jadwal pertandingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all trashed match games.
     */
    public function trashed(): JsonResponse
    {
        try {
            $trashedMatches = MatchGame::onlyTrashed()->with(['timTuanRumah', 'timTamu'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal pertandingan yang terhapus berhasil diambil',
                'data' => $trashedMatches,
                'total' => $trashedMatches->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jadwal pertandingan yang terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a trashed match game.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $matchGame = MatchGame::onlyTrashed()->findOrFail($id);
            $matchGame->restore();
            $matchGame->load(['timTuanRumah', 'timTamu']);

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan {$matchGame->match_description} berhasil dipulihkan",
                'data' => $matchGame
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan jadwal pertandingan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Permanently delete a match game.
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $matchGame = MatchGame::onlyTrashed()->findOrFail($id);
            $matchDescription = $matchGame->match_description;
            $matchGame->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan {$matchDescription} berhasil dihapus permanen"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jadwal pertandingan secara permanen',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get upcoming matches.
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $query = MatchGame::with(['timTuanRumah', 'timTamu'])->upcoming();
            
            if ($request->has('limit')) {
                $matches = $query->limit($request->get('limit'))->get();
            } else {
                $matches = $query->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pertandingan mendatang berhasil diambil',
                'data' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal pertandingan mendatang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's matches.
     */
    public function today(): JsonResponse
    {
        try {
            $matches = MatchGame::with(['timTuanRumah', 'timTamu'])
                              ->today()
                              ->orderBy('waktu_pertandingan')
                              ->get();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pertandingan hari ini berhasil diambil',
                'data' => $matches,
                'count' => $matches->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal pertandingan hari ini',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get matches by team.
     */
    public function getByTeam(Team $team, Request $request): JsonResponse
    {
        try {
            $query = MatchGame::with(['timTuanRumah', 'timTamu'])
                             ->where(function ($q) use ($team) {
                                 $q->where('tim_tuan_rumah_id', $team->id)
                                   ->orWhere('tim_tamu_id', $team->id);
                             });

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status_pertandingan', $request->get('status'));
            }

            // Default to upcoming matches
            if ($request->get('filter') === 'upcoming' || !$request->has('filter')) {
                $query->where('tanggal_pertandingan', '>=', Carbon::today());
            }

            $matches = $query->orderBy('tanggal_pertandingan')
                           ->orderBy('waktu_pertandingan')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan {$team->nama_tim} berhasil diambil",
                'data' => [
                    'team' => $team,
                    'matches' => $matches,
                    'total_matches' => $matches->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal pertandingan tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
