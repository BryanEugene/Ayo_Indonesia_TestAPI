<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FootballMatch extends Model
{
    protected $fillable = [
        'tanggal_pertandingan',
        'waktu_pertandingan',
        'tim_tuan_rumah',
        'tim_tamu',
        'tempat_pertandingan',
        'status_pertandingan'
    ];

    protected $casts = [
        'tanggal_pertandingan' => 'date',
        'waktu_pertandingan' => 'datetime:H:i'
    ];

    /**
     * Get the home team that plays the match.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'tim_tuan_rumah');
    }

    /**
     * Get the away team that plays the match.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'tim_tamu');
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal_pertandingan', $date);
    }

    /**
     * Scope untuk filter berdasarkan tim
     */
    public function scopeByTeam($query, $teamId)
    {
        return $query->where('tim_tuan_rumah', $teamId)
                    ->orWhere('tim_tamu', $teamId);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pertandingan', $status);
    }

    /**
     * Get formatted match date and time
     */
    public function getFormattedDateTimeAttribute()
    {
        return $this->tanggal_pertandingan->format('d/m/Y') . ' ' . $this->waktu_pertandingan->format('H:i');
    }
}
