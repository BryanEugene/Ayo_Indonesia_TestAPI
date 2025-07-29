<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayerRequest extends FormRequest
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
            'team_id' => 'required|exists:teams,id',
            'nama_pemain' => 'required|string|max:255',
            'tinggi_badan' => 'required|integer|min:140|max:220',
            'berat_badan' => 'required|integer|min:40|max:150',
            'posisi_pemain' => 'required|in:Penyerang,Gelandang,Bertahan,Penjaga Gawang',
            'nomor_punggung' => 'required|integer|min:1|max:99'
        ];

        // Validasi untuk memastikan nomor punggung unik dalam tim
        if ($this->isMethod('POST')) {
            $rules['nomor_punggung'] .= '|unique:players,nomor_punggung,null,id,team_id,' . $this->team_id;
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $playerId = $this->route('player') ? $this->route('player')->id : null;
            $rules['nama_pemain'] .= '|unique:players,nama_pemain,' . $playerId;
            $rules['nomor_punggung'] .= '|unique:players,nomor_punggung,' . $playerId . ',id,team_id,' . $this->team_id;
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'team_id.required' => 'Tim wajib dipilih.',
            'team_id.exists' => 'Tim yang dipilih tidak valid.',
            'nama_pemain.required' => 'Nama pemain wajib diisi.',
            'tinggi_badan.required' => 'Tinggi badan wajib diisi.',
            'tinggi_badan.min' => 'Tinggi badan minimal 140 cm.',
            'tinggi_badan.max' => 'Tinggi badan maksimal 220 cm.',
            'berat_badan.required' => 'Berat badan wajib diisi.',
            'berat_badan.min' => 'Berat badan minimal 40 kg.',
            'berat_badan.max' => 'Berat badan maksimal 150 kg.',
            'posisi_pemain.required' => 'Posisi pemain wajib dipilih.',
            'posisi_pemain.in' => 'Posisi pemain harus salah satu dari: Penyerang, Gelandang, Bertahan, Penjaga Gawang.',
            'nomor_punggung.required' => 'Nomor punggung wajib diisi.',
            'nomor_punggung.min' => 'Nomor punggung minimal 1.',
            'nomor_punggung.max' => 'Nomor punggung maksimal 99.',
            'nomor_punggung.unique' => 'Nomor punggung sudah digunakan pemain lain dalam tim ini.',
        ];
    }
}
