<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Team::query();

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama_tim', 'like', "%{$search}%")
                      ->orWhere('kota_markas', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'nama_tim');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $teams = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data tim berhasil diambil',
                'data' => $teams->items(),
                'pagination' => [
                    'current_page' => $teams->currentPage(),
                    'last_page' => $teams->lastPage(),
                    'per_page' => $teams->perPage(),
                    'total' => $teams->total(),
                    'from' => $teams->firstItem(),
                    'to' => $teams->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(TeamRequest $request): JsonResponse
    {
        try {
            $team = Team::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tim berhasil ditambahkan',
                'data' => $team
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team): JsonResponse
    {
        try {
            $team->load(['players' => function ($query) {
                $query->orderBy('nomor_punggung');
            }]);
            
            return response()->json([
                'success' => true,
                'message' => 'Detail tim berhasil diambil',
                'data' => [
                    'team_info' => $team,
                    'total_players' => $team->players->count(),
                    'players_by_position' => [
                        'Penjaga Gawang' => $team->players->where('posisi_pemain', 'Penjaga Gawang')->values(),
                        'Bertahan' => $team->players->where('posisi_pemain', 'Bertahan')->values(),
                        'Gelandang' => $team->players->where('posisi_pemain', 'Gelandang')->values(),
                        'Penyerang' => $team->players->where('posisi_pemain', 'Penyerang')->values(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tim tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified team in storage.
     */
    public function update(TeamRequest $request, Team $team): JsonResponse
    {
        try {
            $team->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tim berhasil diperbarui',
                'data' => $team->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified team from storage (soft delete).
     */
    public function destroy(Team $team): JsonResponse
    {
        try {
            $teamName = $team->nama_tim;
            $team->delete(); // This will soft delete

            return response()->json([
                'success' => true,
                'message' => "Tim {$teamName} berhasil dihapus (soft delete)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all trashed teams.
     */
    public function trashed(): JsonResponse
    {
        try {
            $trashedTeams = Team::onlyTrashed()->withCount('players')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data tim yang terhapus berhasil diambil',
                'data' => $trashedTeams,
                'total' => $trashedTeams->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tim yang terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a trashed team.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $team = Team::onlyTrashed()->findOrFail($id);
            $team->restore();

            return response()->json([
                'success' => true,
                'message' => "Tim {$team->nama_tim} berhasil dipulihkan",
                'data' => $team
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan tim',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Permanently delete a team.
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $team = Team::onlyTrashed()->findOrFail($id);
            $teamName = $team->nama_tim;
            $team->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Tim {$teamName} berhasil dihapus permanen"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tim secara permanen',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get teams by city.
     */
    public function getByCity(Request $request, string $city): JsonResponse
    {
        try {
            $teams = Team::where('kota_markas', 'like', "%{$city}%")->get();

            return response()->json([
                'success' => true,
                'message' => "Tim di kota {$city} berhasil diambil",
                'data' => $teams
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tim berdasarkan kota',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
