<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // form publik
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jabatan'      => ['required', 'string', 'max:255'],
            'unit_kerja'   => ['required', 'string', 'max:255'],
            'tanda_tangan' => [
                'required',
                'string',
                'max:2000000', // batas panjang string base64 (~1,5 MB gambar)
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! preg_match('#^data:image/(png|jpeg);base64,#', $value)) {
                        $fail('Format tanda tangan tidak valid.');
                        return;
                    }

                    $decoded = base64_decode(substr($value, strpos($value, ',') + 1), true);

                    if ($decoded === false || @getimagesizefromstring($decoded) === false) {
                        $fail('Data tanda tangan bukan gambar yang valid.');
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'unit_kerja'   => 'unit kerja',
            'tanda_tangan' => 'tanda tangan',
        ];
    }

    public function messages(): array
    {
        return [
            'required'         => ':Attribute wajib diisi.',
            'max'              => ':Attribute terlalu panjang.',
            'tanda_tangan.max' => 'Ukuran tanda tangan terlalu besar.',
        ];
    }
}