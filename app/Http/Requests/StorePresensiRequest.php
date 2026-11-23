<?php

namespace App\Http\Requests;

use App\Models\Rapat;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StorePresensiRequest extends FormRequest
{
    // Jangan simpan data gambar base64 yang besar ke session saat validasi gagal
    protected $dontFlash = ['password', 'password_confirmation', 'tanda_tangan', 'foto'];

    public function authorize(): bool
    {
        return true; // form publik
    }

    public function rules(): array
    {
        $rules = [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jabatan'      => ['required', 'string', 'max:255'],
            'unit_kerja'   => ['required', 'string', 'max:255'],
            'tanda_tangan' => ['required', 'string', 'max:2000000', $this->gambarBase64(['png', 'jpeg'], 'tanda tangan')],
        ];

        // Foto hanya diminta jika admin mengaktifkannya pada rapat ini
        $rapat = Rapat::where('slug', $this->route('slug'))->first();

        if ($rapat && $rapat->minta_foto) {
            $rules['foto'] = ['required', 'string', 'max:2000000', $this->gambarBase64(['jpeg', 'png'], 'foto')];
        }
        // Email hanya diminta jika admin mengaktifkannya pada rapat ini
        if ($rapat && $rapat->minta_email) {
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }

    /** Pastikan string adalah data URI gambar base64 yang benar-benar bisa dibaca sebagai gambar */
    private function gambarBase64(array $tipe, string $nama): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($tipe, $nama) {
            $pola = '#^data:image/(' . implode('|', $tipe) . ');base64,#';

            if (! preg_match($pola, $value)) {
                $fail("Format {$nama} tidak valid.");
                return;
            }

            $decoded = base64_decode(substr($value, strpos($value, ',') + 1), true);

            if ($decoded === false || @getimagesizefromstring($decoded) === false) {
                $fail("Data {$nama} bukan gambar yang valid.");
            }
        };
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'unit_kerja'   => 'unit kerja',
            'tanda_tangan' => 'tanda tangan',
            'foto'         => 'foto bukti',
        ];
    }

    public function messages(): array
    {
        return [
            'required'         => ':Attribute wajib diisi.',
            'max'              => ':Attribute terlalu panjang.',
            'tanda_tangan.max' => 'Ukuran tanda tangan terlalu besar.',
            'foto.max'         => 'Ukuran foto terlalu besar.',
            'email.email'      => 'Format email tidak valid.',
        ];
    }
}