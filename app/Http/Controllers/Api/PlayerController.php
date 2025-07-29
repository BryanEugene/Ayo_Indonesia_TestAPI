<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerRequest;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Display a listing of players.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Player::with('team');

            // Filter by team
            if ($request->has('team_id')) {
                $query->where('team_id', $request->get('team_id'));
            }

            // Filter by position
            if ($request->has('posisi_pemain')) {
                $query->where('posisi_pemain', $request->get('posisi_pemain'));
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pemain', 'like', "%{$search}%")
                      ->orWhere('posisi_pemain', 'like', "%{$search}%")
                      ->orWhere('nomor_punggung', 'like', "%{$search}%")
                      ->orWhereHas('team', function ($teamQuery) use ($search) {
                          $teamQuery->where('nama_tim', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'nomor_punggung');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $players = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data pemain berhasil diambil',
                'data' => $players->items(),
                'pagination' => [
                    'current_page' => $players->currentPage(),
                    'last_page' => $players->lastPage(),
                    'per_page' => $players->perPage(),
                    'total' => $players->total(),
                    'from' => $players->firstItem(),
                    'to' => $players->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pemain',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created player in storage.
     */
    public function store(PlayerRequest $request): JsonResponse
    {
        try {
            $player = Player::create($request->validated());
            $player->load('team');

            return response()->json([
                'success' => true,
                'message' => 'Pemain berhasil ditambahkan',
                'data' => $player
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pemain',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified player.
     */
    public function show(Player $player): JsonResponse
    {
        try {
            $player->load('team');
            
            return response()->json([
                'success' => true,
                'message' => 'Detail pemain berhasil diambil',
                'data' => $player
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pemain tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified player in storage.
     */
    public function update(PlayerRequest $request, Player $player): JsonResponse
    {
        try {
            $player->update($request->validated());
            $player->load('team');

            return response()->json([
                'success' => true,
                'message' => 'Data pemain berhasil diperbarui',
                'data' => $player
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pemain',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified player from storage (soft delete).
     */
    public function destroy(Player $player): JsonResponse
    {
        try {
            $playerName = $player->nama_pemain;
            $player->delete(); // This will soft delete

            return response()->json([
                'success' => true,
                'message' => "Pemain {$playerName} berhasil dihapus (soft delete)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pemain',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all trashed players.
     */
    public function trashed(): JsonResponse
    {
        try {
            $trashedPlayers = Player::onlyTrashed()->with('team')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pemain yang terhapus berhasil diambil',
                'data' => $trashedPlayers,
                'total' => $trashedPlayers->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pemain yang terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a trashed player.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $player = Player::onlyTrashed()->findOrFail($id);
            $player->restore();
            $player->load('team');

            return response()->json([
                'success' => true,
                'message' => "Pemain {$player->nama_pemain} berhasil dipulihkan",
                'data' => $player
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan pemain',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Permanently delete a player.
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $player = Player::onlyTrashed()->findOrFail($id);
            $playerName = $player->nama_pemain;
            $player->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Pemain {$playerName} berhasil dihapus permanen"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pemain secara permanen',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get players by team.
     */
    public function getByTeam(Team $team): JsonResponse
    {
        try {
            $players = $team->players()->orderBy('nomor_punggung')->get();

            return response()->json([
                'success' => true,
                'message' => "Pemain tim {$team->nama_tim} berhasil diambil",
                'data' => [
                    'team' => $team,
                    'players' => $players,
                    'total_players' => $players->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pemain tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get players by position.
     */
    public function getByPosition(Request $request, string $position): JsonResponse
    {
        try {
            $validPositions = ['Penyerang', 'Gelandang', 'Bertahan', 'Penjaga Gawang'];
            
            if (!in_array($position, $validPositions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Posisi tidak valid',
                    'valid_positions' => $validPositions
                ], 400);
            }

            $query = Player::with('team')->where('posisi_pemain', $position);
            
            if ($request->has('team_id')) {
                $query->where('team_id', $request->get('team_id'));
            }
            
            $players = $query->orderBy('nomor_punggung')->get();

            return response()->json([
                'success' => true,
                'message' => "Pemain dengan posisi {$position} berhasil diambil",
                'data' => $players
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pemain berdasarkan posisi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
