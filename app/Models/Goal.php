<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'match_result_id',
        'player_id',
        'team_id',
        'menit_gol',
        'detik_gol',
        'jenis_gol',
        'deskripsi_gol'
    ];

    protected $casts = [
        'menit_gol' => 'integer',
        'detik_gol' => 'integer'
    ];

    /**
     * Get the match result that owns the goal.
     */
    public function matchResult(): BelongsTo
    {
        return $this->belongsTo(MatchResult::class);
    }

    /**
     * Get the player who scored the goal.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the team that scored the goal.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get formatted goal time
     */
    public function getFormattedTimeAttribute()
    {
        if ($this->detik_gol > 0) {
            return $this->menit_gol . ":" . str_pad($this->detik_gol, 2, '0', STR_PAD_LEFT);
        }
        return $this->menit_gol . "'";
    }

    /**
     * Get goal description with player and time
     */
    public function getGoalDescriptionAttribute()
    {
        $description = $this->player->nama_pemain . " (" . $this->formatted_time . ")";
        
        if ($this->jenis_gol !== 'Normal') {
            $description .= " - " . $this->jenis_gol;
        }
        
        return $description;
    }

    /**
     * Scope untuk filter berdasarkan pemain
     */
    public function scopeByPlayer($query, $playerId)
    {
        return $query->where('player_id', $playerId);
    }

    /**
     * Scope untuk filter berdasarkan tim
     */
    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope untuk filter berdasarkan jenis gol
     */
    public function scopeByType($query, $type)
    {
        return $query->where('jenis_gol', $type);
    }

    /**
     * Scope untuk mengurutkan berdasarkan waktu
     */
    public function scopeOrderByTime($query)
    {
        return $query->orderBy('menit_gol')->orderBy('detik_gol');
    }
}
