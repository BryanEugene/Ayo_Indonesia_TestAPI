<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamRequest extends FormRequest
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
            'nama_tim' => 'required|string|max:255',
            'logo_tim' => 'nullable|string|max:255',
            'tahun_berdiri' => 'required|integer|min:1900|max:' . date('Y'),
            'alamat_markas' => 'required|string',
            'kota_markas' => 'required|string|max:255',
        ];

        // For update operations, make nama_tim unique except for current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $teamId = $this->route('team') ? $this->route('team')->id : null;
            $rules['nama_tim'] .= '|unique:teams,nama_tim,' . $teamId;
        } else {
            $rules['nama_tim'] .= '|unique:teams,nama_tim';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nama_tim.required' => 'Nama tim wajib diisi.',
            'nama_tim.unique' => 'Nama tim sudah ada.',
            'tahun_berdiri.required' => 'Tahun berdiri wajib diisi.',
            'tahun_berdiri.min' => 'Tahun berdiri tidak valid.',
            'tahun_berdiri.max' => 'Tahun berdiri tidak boleh lebih dari tahun sekarang.',
            'alamat_markas.required' => 'Alamat markas wajib diisi.',
            'kota_markas.required' => 'Kota markas wajib diisi.',
        ];
    }
}
