<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchResult extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'match_game_id',
        'skor_tim_tuan_rumah',
        'skor_tim_tamu',
        'catatan_hasil',
        'waktu_laporan'
    ];

    protected $casts = [
        'skor_tim_tuan_rumah' => 'integer',
        'skor_tim_tamu' => 'integer',
        'waktu_laporan' => 'datetime'
    ];

    /**
     * Get the match game that owns the result.
     */
    public function matchGame(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class);
    }

    /**
     * Get the goals for the match result.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get goals ordered by time
     */
    public function goalsOrderedByTime(): HasMany
    {
        return $this->hasMany(Goal::class)->orderBy('menit_gol')->orderBy('detik_gol');
    }

    /**
     * Get goals by team
     */
    public function goalsByTeam($teamId)
    {
        return $this->goals()->where('team_id', $teamId)->orderBy('menit_gol')->orderBy('detik_gol')->get();
    }

    /**
     * Get match result summary
     */
    public function getResultSummaryAttribute()
    {
        return $this->skor_tim_tuan_rumah . ' - ' . $this->skor_tim_tamu;
    }

    /**
     * Get match winner
     */
    public function getWinnerAttribute()
    {
        if ($this->skor_tim_tuan_rumah > $this->skor_tim_tamu) {
            return 'home';
        } elseif ($this->skor_tim_tamu > $this->skor_tim_tuan_rumah) {
            return 'away';
        } else {
            return 'draw';
        }
    }

    /**
     * Get winner team name
     */
    public function getWinnerTeamNameAttribute()
    {
        $match = $this->matchGame;
        if (!$match) return null;

        switch ($this->winner) {
            case 'home':
                return $match->timTuanRumah->nama_tim;
            case 'away':
                return $match->timTamu->nama_tim;
            default:
                return 'Seri';
        }
    }

    /**
     * Check if match result is complete
     */
    public function isComplete(): bool
    {
        return $this->matchGame && $this->matchGame->status_pertandingan === 'Selesai';
    }
}
