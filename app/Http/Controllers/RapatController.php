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

        // Cegah presensi ganda dengan NIP/NIK yang sama pada rapat yang sama
        if (! empty($data['nip'])
            && $rapat->absensis()->where('nip', $data['nip'])->exists()) {
            return back()
                ->withInput($request->except('tanda_tangan')) // jangan flash base64 besar ke session
                ->withErrors(['nip' => 'NIP/NIK ini sudah tercatat hadir pada rapat ini.']);
        }

        $rapat->absensis()->create([
            ...$data,
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