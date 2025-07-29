<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'team_id',
        'nama_pemain',
        'tinggi_badan',
        'berat_badan',
        'posisi_pemain',
        'nomor_punggung'
    ];

    protected $casts = [
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'nomor_punggung' => 'integer'
    ];

    /**
     * Get the team that owns the player.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope untuk filter berdasarkan posisi
     */
    public function scopeByPosition($query, $position)
    {
        return $query->where('posisi_pemain', $position);
    }

    /**
     * Get the goals scored by the player.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Scope untuk filter berdasarkan tim
     */
    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Get total goals scored by player
     */
    public function getTotalGoalsAttribute()
    {
        return $this->goals()->count();
    }
}
