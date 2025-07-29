<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nama_tim',
        'logo_tim',
        'tahun_berdiri',
        'alamat_markas',
        'kota_markas'
    ];

    protected $casts = [
        'tahun_berdiri' => 'integer'
    ];

    /**
     * Get the players for the team.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the home matches for the team.
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'tim_tuan_rumah_id');
    }

    /**
     * Get the away matches for the team.
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'tim_tamu_id');
    }

    /**
     * Get all matches for the team (both home and away).
     */
    public function allMatches()
    {
        return MatchGame::where('tim_tuan_rumah_id', $this->id)
                       ->orWhere('tim_tamu_id', $this->id);
    }

    /**
     * Get players by position
     */
    public function playersByPosition($position)
    {
        return $this->players()->where('posisi_pemain', $position);
    }
}
