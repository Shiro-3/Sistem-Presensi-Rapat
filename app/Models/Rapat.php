<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Rapat extends Model
{
    use HasFactory;

    protected $table = 'rapats';

    protected $fillable = [
        'slug',
        'judul_rapat',
        'penyelenggara',
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
    
    public const TIPE_LOKASI = ['Daring', 'Luring', 'Daring & Luring'];

    // Otomatis ikut tampil di toArray()/JSON response API
    protected $appends = ['status_otomatis'];

    protected static function booted(): void
    {
        // Generate slug otomatis jika belum diisi
        static::creating(function (Rapat $rapat) {
            if (empty($rapat->slug)) {
                $rapat->slug = Str::slug($rapat->judul_rapat) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    // Route model binding pakai slug: /rapat/{rapat:slug} atau otomatis di bawah
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'rapat_id');
    }

    /**
     * Status otomatis: 'selesai' jika sekarang sudah lewat
     * gabungan tanggal_rapat + jam_selesai, selain itu ikuti status asli.
     */
    public function getStatusOtomatisAttribute(): string
    {
        $waktuSelesai = Carbon::parse(
            $this->tanggal_rapat->format('Y-m-d') . ' ' . $this->jam_selesai
        );

        if (Carbon::now()->greaterThan($waktuSelesai)) {
            return 'selesai';
        }

        return $this->status;
    }
    public function isAktif(): bool
    {
        return $this->status_otomatis === 'aktif';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_otomatis === 'aktif' ? 'Aktif' : 'Rapat Selesai';
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif')
            ->whereRaw('TIMESTAMP(tanggal_rapat, jam_selesai) >= ?', [now()->toDateTimeString()]);
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', 'selesai')
            ->orWhereRaw('TIMESTAMP(tanggal_rapat, jam_selesai) < ?', [now()->toDateTimeString()]);
        });
    }

    public function scopeCari(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function (Builder $q) use ($keyword) {
            $q->where(function (Builder $q) use ($keyword) {
                $q->where('judul_rapat', 'like', "%{$keyword}%")
                ->orWhere('penyelenggara', 'like', "%{$keyword}%");
            });
        });
    }
}
