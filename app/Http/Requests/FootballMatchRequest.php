<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FootballMatchRequest extends FormRequest
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
        return [
            'tanggal_pertandingan' => 'required|date|after_or_equal:today',
            'waktu_pertandingan' => 'required|date_format:H:i',
            'tim_tuan_rumah' => [
                'required',
                'exists:teams,id',
                'different:tim_tamu'
            ],
            'tim_tamu' => [
                'required', 
                'exists:teams,id',
                'different:tim_tuan_rumah'
            ],
            'tempat_pertandingan' => 'nullable|string|max:255',
            'status_pertandingan' => 'nullable|in:Terjadwal,Berlangsung,Selesai,Ditunda,Dibatalkan'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'tanggal_pertandingan.required' => 'Tanggal pertandingan wajib diisi.',
            'tanggal_pertandingan.date' => 'Format tanggal pertandingan tidak valid.',
            'tanggal_pertandingan.after_or_equal' => 'Tanggal pertandingan tidak boleh kurang dari hari ini.',
            'waktu_pertandingan.required' => 'Waktu pertandingan wajib diisi.',
            'waktu_pertandingan.date_format' => 'Format waktu harus HH:MM (contoh: 15:30).',
            'tim_tuan_rumah.required' => 'Tim tuan rumah wajib dipilih.',
            'tim_tuan_rumah.exists' => 'Tim tuan rumah tidak valid.',
            'tim_tuan_rumah.different' => 'Tim tuan rumah harus berbeda dengan tim tamu.',
            'tim_tamu.required' => 'Tim tamu wajib dipilih.',
            'tim_tamu.exists' => 'Tim tamu tidak valid.',
            'tim_tamu.different' => 'Tim tamu harus berbeda dengan tim tuan rumah.',
            'status_pertandingan.in' => 'Status pertandingan harus salah satu dari: Terjadwal, Berlangsung, Selesai, Ditunda, Dibatalkan.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validasi tambahan: pastikan tidak ada pertandingan yang bertabrakan untuk tim yang sama
            if ($this->tanggal_pertandingan && $this->waktu_pertandingan && 
                $this->tim_tuan_rumah && $this->tim_tamu) {
                
                $conflictingMatch = \App\Models\FootballMatch::where('tanggal_pertandingan', $this->tanggal_pertandingan)
                    ->where('waktu_pertandingan', $this->waktu_pertandingan)
                    ->where(function ($query) {
                        $query->where('tim_tuan_rumah', $this->tim_tuan_rumah)
                              ->orWhere('tim_tamu', $this->tim_tuan_rumah)
                              ->orWhere('tim_tuan_rumah', $this->tim_tamu)
                              ->orWhere('tim_tamu', $this->tim_tamu);
                    });

                // Untuk update, kecualikan record yang sedang diupdate
                if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                    $conflictingMatch->where('id', '!=', $this->route('football_match'));
                }

                if ($conflictingMatch->exists()) {
                    $validator->errors()->add('waktu_pertandingan', 'Salah satu tim sudah memiliki jadwal pertandingan di waktu yang sama.');
                }
            }
        });
    }
}
