<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'rapat_id',
        'nama_lengkap',
        'jabatan',
        'unit_kerja',
        'tanda_tangan',
        'waktu_hadir',
    ];

    protected $casts = [
        'waktu_hadir' => 'datetime',
    ];

    public function rapat(): BelongsTo
    {
        return $this->belongsTo(Rapat::class, 'rapat_id');
    }
}