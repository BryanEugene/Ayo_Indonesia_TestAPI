<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MatchGame extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tanggal_pertandingan',
        'waktu_pertandingan',
        'tim_tuan_rumah_id',
        'tim_tamu_id',
        'tempat_pertandingan',
        'status_pertandingan',
        'catatan'
    ];

    protected $casts = [
        'tanggal_pertandingan' => 'date',
        'waktu_pertandingan' => 'datetime:H:i'
    ];

    /**
     * Get the home team.
     */
    public function timTuanRumah(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'tim_tuan_rumah_id');
    }

    /**
     * Get the away team.
     */
    public function timTamu(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'tim_tamu_id');
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal_pertandingan', $date);
    }

    /**
     * Scope untuk filter berdasarkan tim (baik sebagai tuan rumah atau tamu)
     */
    public function scopeByTeam($query, $teamId)
    {
        return $query->where('tim_tuan_rumah_id', $teamId)
                    ->orWhere('tim_tamu_id', $teamId);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pertandingan', $status);
    }

    /**
     * Scope untuk pertandingan yang akan datang
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_pertandingan', '>=', Carbon::today())
                    ->where('status_pertandingan', 'Dijadwalkan')
                    ->orderBy('tanggal_pertandingan')
                    ->orderBy('waktu_pertandingan');
    }

    /**
     * Scope untuk pertandingan hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_pertandingan', Carbon::today());
    }

    /**
     * Get the match result.
     */
    public function matchResult(): HasOne
    {
        return $this->hasOne(MatchResult::class);
    }

    /**
     * Accessor untuk format waktu yang lebih user-friendly
     */
    public function getFormattedDateTimeAttribute()
    {
        return Carbon::parse($this->tanggal_pertandingan->format('Y-m-d') . ' ' . $this->waktu_pertandingan->format('H:i:s'))
                    ->format('d/m/Y H:i');
    }

    /**
     * Get match result as string
     */
    public function getMatchDescriptionAttribute()
    {
        return $this->timTuanRumah->nama_tim . ' vs ' . $this->timTamu->nama_tim;
    }

    /**
     * Check if match has result
     */
    public function hasResult(): bool
    {
        return $this->matchResult !== null;
    }

    /**
     * Get match score summary
     */
    public function getScoreSummaryAttribute()
    {
        if ($this->hasResult()) {
            return $this->matchResult->result_summary;
        }
        return 'Belum ada hasil';
    }
}
