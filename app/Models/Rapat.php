<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Rapat extends Model
{
    use HasFactory;

    public const TIPE_LOKASI = ['Daring', 'Luring', 'Daring & Luring'];

    protected $table = 'rapats';

    protected $fillable = [
        'slug',
        'judul_rapat',
        'tanggal_rapat',
        'jam_mulai',
        'jam_selesai',
        'tipe_lokasi',
        'detail_lokasi',
        'status',
    ];

    protected $casts = [
        'tanggal_rapat' => 'date:Y-m-d',
    ];

    protected $appends = ['status_otomatis'];

    protected static function booted(): void
    {
        // Buat slug otomatis jika belum diisi
        static::creating(function (Rapat $rapat) {
            if (empty($rapat->slug)) {
                $rapat->slug = (Str::slug($rapat->judul_rapat) ?: 'rapat')
                    . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'rapat_id');
    }

    /** 'selesai' jika sekarang sudah lewat tanggal_rapat + jam_selesai, selain itu ikut status asli */
    public function getStatusOtomatisAttribute(): string
    {
        $waktuSelesai = Carbon::parse(
            $this->tanggal_rapat->format('Y-m-d') . ' ' . $this->jam_selesai
        );

        return Carbon::now()->greaterThan($waktuSelesai) ? 'selesai' : $this->status;
    }

    public function isAktif(): bool
    {
        return $this->status_otomatis === 'aktif';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_otomatis === 'aktif' ? 'Aktif' : 'Rapat Selesai';
    }

    protected static function sqlWaktuSelesai(): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? '(tanggal_rapat + jam_selesai)'
            : 'TIMESTAMP(tanggal_rapat, jam_selesai)';
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif')
            ->whereRaw(static::sqlWaktuSelesai() . ' >= ?', [now()->toDateTimeString()]);
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', 'selesai')
              ->orWhereRaw(static::sqlWaktuSelesai() . ' < ?', [now()->toDateTimeString()]);
        });
    }

    public function scopeCari(Builder $query, ?string $keyword): Builder
    {
        $op = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $query->when($keyword, function (Builder $q) use ($keyword, $op) {
            $q->where(function (Builder $q) use ($keyword, $op) {
                $q->where('judul_rapat', $op, "%{$keyword}%");
            });
        });
    }
}