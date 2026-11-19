<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePresensiRequest;
use App\Models\Rapat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class RapatController extends Controller
{
    private const FILTERS = [
        'semua'   => 'Semua',
        'aktif'   => 'Aktif',
        'selesai' => 'Selesai',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $filter = $request->query('status', 'semua');

        if (! array_key_exists($filter, self::FILTERS)) {
            $filter = 'semua';
        }

        $rapats = Rapat::query()
            ->withCount('absensis')
            ->cari($search)
            ->when($filter === 'aktif', fn ($q) => $q->aktif())
            ->when($filter === 'selesai', fn ($q) => $q->selesai())
            ->orderByDesc('tanggal_rapat')
            ->orderByDesc('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('rapat.index', [
            'rapats'        => $rapats,
            'search'        => $search,
            'filter'        => $filter,
            'filterOptions' => self::FILTERS,
        ]);
    }

    public function showForm(string $slug): View|Response
    {
        $rapat = Rapat::where('slug', $slug)->firstOrFail();

        if (! $rapat->isAktif()) {
            return response()->view('rapat.ditutup', compact('rapat'), 403);
        }

        return view('rapat.form', compact('rapat'));
    }

    public function storePresensi(StorePresensiRequest $request, string $slug): RedirectResponse|Response
    {
        $rapat = Rapat::where('slug', $slug)->firstOrFail();

        // Cek ulang saat submit: rapat bisa saja berakhir selagi form terbuka
        if (! $rapat->isAktif()) {
            return response()->view('rapat.ditutup', compact('rapat'), 403);
        }
        $data = $request->validated();

        $sudahAda = $rapat->absensis()
            ->whereRaw('LOWER(nama_lengkap) = ?', [mb_strtolower(trim($data['nama_lengkap']))])
            ->whereRaw('LOWER(unit_kerja) = ?', [mb_strtolower(trim($data['unit_kerja']))])
            ->exists();

        if ($sudahAda) {
            return back()
                ->withInput($request->except('tanda_tangan'))
                ->withErrors(['nama_lengkap' => 'Nama ini sudah tercatat hadir pada rapat ini.']);
        }

        $rapat->absensis()->create([
            ...$request->validated(),
            'waktu_hadir' => now(),
        ]);

        return redirect()
            ->route('rapat.form', $rapat->slug)
            ->with('success', 'Presensi berhasil dicatat. Terima kasih.');
    }
    public function counts(Request $request): JsonResponse
    {
        $slugs = array_filter((array) $request->query('slugs', []), 'is_string');

        $counts = Rapat::whereIn('slug', $slugs)
            ->withCount('absensis')
            ->pluck('absensis_count', 'slug');

        return response()->json($counts);
    }
}