<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\MatchGame;

class MatchResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'match_game_id' => 'required|exists:match_games,id',
            'skor_tim_tuan_rumah' => 'required|integer|min:0|max:20',
            'skor_tim_tamu' => 'required|integer|min:0|max:20',
            'catatan_hasil' => 'nullable|string|max:1000',
            'waktu_laporan' => 'nullable|date',
            
            // Goals validation
            'goals' => 'nullable|array',
            'goals.*.player_id' => 'required_with:goals|exists:players,id',
            'goals.*.team_id' => 'required_with:goals|exists:teams,id',
            'goals.*.menit_gol' => 'required_with:goals|integer|min:1|max:120',
            'goals.*.detik_gol' => 'nullable|integer|min:0|max:59',
            'goals.*.jenis_gol' => 'nullable|in:Normal,Penalti,Own Goal,Free Kick',
            'goals.*.deskripsi_gol' => 'nullable|string|max:500'
        ];

        // Validasi tambahan untuk memastikan match sudah selesai atau sedang berlangsung
        $rules['match_status_check'] = [
            function ($attribute, $value, $fail) {
                if ($this->has('match_game_id')) {
                    $match = MatchGame::find($this->match_game_id);
                    if ($match && !in_array($match->status_pertandingan, ['Selesai', 'Berlangsung'])) {
                        $fail('Hasil hanya dapat dilaporkan untuk pertandingan yang sedang berlangsung atau sudah selesai.');
                    }
                }
            }
        ];

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'match_game_id.required' => 'ID pertandingan wajib diisi.',
            'match_game_id.exists' => 'Pertandingan tidak ditemukan.',
            'skor_tim_tuan_rumah.required' => 'Skor tim tuan rumah wajib diisi.',
            'skor_tim_tuan_rumah.integer' => 'Skor tim tuan rumah harus berupa angka.',
            'skor_tim_tuan_rumah.min' => 'Skor tim tuan rumah tidak boleh negatif.',
            'skor_tim_tuan_rumah.max' => 'Skor tim tuan rumah maksimal 20.',
            'skor_tim_tamu.required' => 'Skor tim tamu wajib diisi.',
            'skor_tim_tamu.integer' => 'Skor tim tamu harus berupa angka.',
            'skor_tim_tamu.min' => 'Skor tim tamu tidak boleh negatif.',
            'skor_tim_tamu.max' => 'Skor tim tamu maksimal 20.',
            'catatan_hasil.max' => 'Catatan hasil maksimal 1000 karakter.',
            'waktu_laporan.date' => 'Format waktu laporan tidak valid.',
            
            // Goals validation messages
            'goals.array' => 'Data gol harus berupa array.',
            'goals.*.player_id.required_with' => 'ID pemain wajib diisi untuk setiap gol.',
            'goals.*.player_id.exists' => 'Pemain tidak ditemukan.',
            'goals.*.team_id.required_with' => 'ID tim wajib diisi untuk setiap gol.',
            'goals.*.team_id.exists' => 'Tim tidak ditemukan.',
            'goals.*.menit_gol.required_with' => 'Menit gol wajib diisi.',
            'goals.*.menit_gol.integer' => 'Menit gol harus berupa angka.',
            'goals.*.menit_gol.min' => 'Menit gol minimal 1.',
            'goals.*.menit_gol.max' => 'Menit gol maksimal 120.',
            'goals.*.detik_gol.integer' => 'Detik gol harus berupa angka.',
            'goals.*.detik_gol.min' => 'Detik gol minimal 0.',
            'goals.*.detik_gol.max' => 'Detik gol maksimal 59.',
            'goals.*.jenis_gol.in' => 'Jenis gol harus salah satu dari: Normal, Penalti, Own Goal, Free Kick.',
            'goals.*.deskripsi_gol.max' => 'Deskripsi gol maksimal 500 karakter.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default waktu laporan jika tidak ada
        if (!$this->has('waktu_laporan')) {
            $this->merge([
                'waktu_laporan' => now()
            ]);
        }

        // Set default jenis gol untuk setiap gol
        if ($this->has('goals') && is_array($this->goals)) {
            $goals = $this->goals;
            foreach ($goals as $index => $goal) {
                if (!isset($goal['jenis_gol'])) {
                    $goals[$index]['jenis_gol'] = 'Normal';
                }
                if (!isset($goal['detik_gol'])) {
                    $goals[$index]['detik_gol'] = 0;
                }
            }
            $this->merge(['goals' => $goals]);
        }
    }

    /**
     * Validasi khusus setelah validasi dasar
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('goals') && $this->has('skor_tim_tuan_rumah') && $this->has('skor_tim_tamu')) {
                $totalGoals = count($this->goals ?? []);
                $totalScore = $this->skor_tim_tuan_rumah + $this->skor_tim_tamu;
                
                // Validasi bahwa jumlah gol sesuai dengan total skor (opsional, bisa dimatikan jika ada own goal)
                // if ($totalGoals !== $totalScore) {
                //     $validator->errors()->add('goals', 'Jumlah gol tidak sesuai dengan total skor.');
                // }

                // Validasi bahwa pemain yang mencetak gol adalah bagian dari salah satu tim yang bertanding
                if ($this->has('match_game_id') && is_array($this->goals)) {
                    $match = MatchGame::with(['timTuanRumah.players', 'timTamu.players'])->find($this->match_game_id);
                    if ($match) {
                        $validPlayerIds = $match->timTuanRumah->players->pluck('id')
                                        ->merge($match->timTamu->players->pluck('id'))
                                        ->toArray();
                        
                        foreach ($this->goals as $index => $goal) {
                            if (isset($goal['player_id']) && !in_array($goal['player_id'], $validPlayerIds)) {
                                $validator->errors()->add("goals.{$index}.player_id", 'Pemain tidak bermain dalam pertandingan ini.');
                            }
                        }
                    }
                }
            }
        });
    }
}
