<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatchGameRequest extends FormRequest
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
            'tanggal_pertandingan' => 'required|date|after_or_equal:today',
            'waktu_pertandingan' => 'required|date_format:H:i',
            'tim_tuan_rumah_id' => 'required|exists:teams,id',
            'tim_tamu_id' => 'required|exists:teams,id|different:tim_tuan_rumah_id',
            'tempat_pertandingan' => 'nullable|string|max:255',
            'status_pertandingan' => 'sometimes|in:Dijadwalkan,Berlangsung,Selesai,Dibatalkan',
            'catatan' => 'nullable|string|max:1000'
        ];

        // Validasi untuk memastikan tidak ada pertandingan bersamaan untuk tim yang sama
        if ($this->isMethod('POST')) {
            $rules['tim_conflict'] = [
                function ($attribute, $value, $fail) {
                    $existingMatch = \App\Models\MatchGame::where('tanggal_pertandingan', $this->tanggal_pertandingan)
                        ->where('waktu_pertandingan', $this->waktu_pertandingan)
                        ->where(function ($query) {
                            $query->where('tim_tuan_rumah_id', $this->tim_tuan_rumah_id)
                                  ->orWhere('tim_tamu_id', $this->tim_tuan_rumah_id)
                                  ->orWhere('tim_tuan_rumah_id', $this->tim_tamu_id)
                                  ->orWhere('tim_tamu_id', $this->tim_tamu_id);
                        })
                        ->where('status_pertandingan', '!=', 'Dibatalkan')
                        ->exists();

                    if ($existingMatch) {
                        $fail('Salah satu tim sudah memiliki pertandingan pada waktu yang sama.');
                    }
                }
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'tanggal_pertandingan.required' => 'Tanggal pertandingan wajib diisi.',
            'tanggal_pertandingan.date' => 'Format tanggal tidak valid.',
            'tanggal_pertandingan.after_or_equal' => 'Tanggal pertandingan tidak boleh di masa lalu.',
            'waktu_pertandingan.required' => 'Waktu pertandingan wajib diisi.',
            'waktu_pertandingan.date_format' => 'Format waktu harus HH:MM (contoh: 15:30).',
            'tim_tuan_rumah_id.required' => 'Tim tuan rumah wajib dipilih.',
            'tim_tuan_rumah_id.exists' => 'Tim tuan rumah yang dipilih tidak valid.',
            'tim_tamu_id.required' => 'Tim tamu wajib dipilih.',
            'tim_tamu_id.exists' => 'Tim tamu yang dipilih tidak valid.',
            'tim_tamu_id.different' => 'Tim tamu harus berbeda dengan tim tuan rumah.',
            'status_pertandingan.in' => 'Status pertandingan harus salah satu dari: Dijadwalkan, Berlangsung, Selesai, Dibatalkan.',
            'tempat_pertandingan.max' => 'Tempat pertandingan maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default status if not provided
        if (!$this->has('status_pertandingan')) {
            $this->merge([
                'status_pertandingan' => 'Dijadwalkan'
            ]);
        }
    }
}
