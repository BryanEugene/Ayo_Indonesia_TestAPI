<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FootballMatchRequest;
use App\Models\FootballMatch;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FootballMatchController extends Controller
{
    /**
     * Display a listing of football matches.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FootballMatch::with(['homeTeam', 'awayTeam']);

            // Filter by date
            if ($request->has('tanggal')) {
                $query->whereDate('tanggal_pertandingan', $request->get('tanggal'));
            }

            // Filter by team
            if ($request->has('team_id')) {
                $teamId = $request->get('team_id');
                $query->where(function ($q) use ($teamId) {
                    $q->where('tim_tuan_rumah', $teamId)
                      ->orWhere('tim_tamu', $teamId);
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status_pertandingan', $request->get('status'));
            }

            // Filter by date range
            if ($request->has('dari_tanggal') && $request->has('sampai_tanggal')) {
                $query->whereBetween('tanggal_pertandingan', [
                    $request->get('dari_tanggal'),
                    $request->get('sampai_tanggal')
                ]);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('tempat_pertandingan', 'like', "%{$search}%")
                      ->orWhereHas('homeTeam', function ($teamQuery) use ($search) {
                          $teamQuery->where('nama_tim', 'like', "%{$search}%");
                      })
                      ->orWhereHas('awayTeam', function ($teamQuery) use ($search) {
                          $teamQuery->where('nama_tim', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'tanggal_pertandingan');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if ($sortBy === 'tanggal_pertandingan') {
                $query->orderBy('tanggal_pertandingan', $sortDirection)
                      ->orderBy('waktu_pertandingan', $sortDirection);
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $matches = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal pertandingan berhasil diambil',
                'data' => $matches->items(),
                'pagination' => [
                    'current_page' => $matches->currentPage(),
                    'last_page' => $matches->lastPage(),
                    'per_page' => $matches->perPage(),
                    'total' => $matches->total(),
                    'from' => $matches->firstItem(),
                    'to' => $matches->lastItem(),
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
     * Store a newly created football match in storage.
     */
    public function store(FootballMatchRequest $request): JsonResponse
    {
        try {
            $match = FootballMatch::create($request->validated());
            $match->load(['homeTeam', 'awayTeam']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pertandingan berhasil ditambahkan',
                'data' => $match
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
     * Display the specified football match.
     */
    public function show(FootballMatch $footballMatch): JsonResponse
    {
        try {
            $footballMatch->load(['homeTeam', 'awayTeam']);
            
            return response()->json([
                'success' => true,
                'message' => 'Detail jadwal pertandingan berhasil diambil',
                'data' => $footballMatch
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
     * Update the specified football match in storage.
     */
    public function update(FootballMatchRequest $request, FootballMatch $footballMatch): JsonResponse
    {
        try {
            $footballMatch->update($request->validated());
            $footballMatch->load(['homeTeam', 'awayTeam']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pertandingan berhasil diperbarui',
                'data' => $footballMatch
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
     * Remove the specified football match from storage.
     */
    public function destroy(FootballMatch $footballMatch): JsonResponse
    {
        try {
            $matchInfo = $footballMatch->homeTeam->nama_tim . ' vs ' . $footballMatch->awayTeam->nama_tim;
            $footballMatch->delete();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan {$matchInfo} berhasil dihapus"
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
     * Get matches by team.
     */
    public function getByTeam(Team $team): JsonResponse
    {
        try {
            $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->where(function ($query) use ($team) {
                    $query->where('tim_tuan_rumah', $team->id)
                          ->orWhere('tim_tamu', $team->id);
                })
                ->orderBy('tanggal_pertandingan')
                ->orderBy('waktu_pertandingan')
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan tim {$team->nama_tim} berhasil diambil",
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

    /**
     * Get matches by date.
     */
    public function getByDate(Request $request, string $date): JsonResponse
    {
        try {
            $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->whereDate('tanggal_pertandingan', $date)
                ->orderBy('waktu_pertandingan')
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan tanggal {$date} berhasil diambil",
                'data' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal pertandingan berdasarkan tanggal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming matches.
     */
    public function getUpcoming(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 7); // Default 7 hari ke depan
            
            $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->where('tanggal_pertandingan', '>=', Carbon::today())
                ->where('tanggal_pertandingan', '<=', Carbon::today()->addDays($days))
                ->where('status_pertandingan', 'Terjadwal')
                ->orderBy('tanggal_pertandingan')
                ->orderBy('waktu_pertandingan')
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Jadwal pertandingan {$days} hari ke depan berhasil diambil",
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
}
