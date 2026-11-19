<?php

namespace App\Http\Requests;

use App\Models\Rapat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRapatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'judul_rapat'   => ['required', 'string', 'max:255'],
            'tanggal_rapat' => ['required', 'date'],
            'jam_mulai'     => ['required', 'date_format:H:i'],
            'jam_selesai'   => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'tipe_lokasi'   => ['required', Rule::in(Rapat::TIPE_LOKASI)],
            'detail_lokasi' => ['required', 'string', 'max:1000'],
            'minta_foto' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'judul_rapat'   => 'judul rapat',
            'tanggal_rapat' => 'tanggal rapat',
            'jam_mulai'     => 'jam mulai',
            'jam_selesai'   => 'jam selesai',
            'tipe_lokasi'   => 'tipe lokasi',
            'detail_lokasi' => 'detail lokasi',
            'minta_foto'    => 'foto bukti',
        ];
    }

    public function messages(): array
    {
        return [
            'required'          => ':Attribute wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'date_format'       => 'Format :attribute tidak valid.',
        ];
    }
}