<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRapatRequest;
use App\Models\Absensi;
use App\Models\Rapat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    private const FILTERS = [
        'semua'   => 'Semua',
        'aktif'   => 'Aktif',
        'selesai' => 'Selesai',
    ];

    /** Dashboard: statistik + daftar rapat */
    public function dashboard(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $filter = $request->query('status', 'semua');

        if (! array_key_exists($filter, self::FILTERS)) {
            $filter = 'semua';
        }

        $stats = [
            'total'   => Rapat::count(),
            'aktif'   => Rapat::aktif()->count(),
            'selesai' => Rapat::selesai()->count(),
            'hadir'   => Absensi::count(),
        ];

        $rapats = Rapat::query()
            ->withCount('absensis')
            ->cari($search)
            ->when($filter === 'aktif', fn ($q) => $q->aktif())
            ->when($filter === 'selesai', fn ($q) => $q->selesai())
            ->orderByDesc('tanggal_rapat')
            ->orderByDesc('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('admin.dashboard', [
            'stats'         => $stats,
            'rapats'        => $rapats,
            'search'        => $search,
            'filter'        => $filter,
            'filterOptions' => self::FILTERS,
        ]);
    }

    public function create(): View
    {
        return view('admin.create', ['tipeLokasi' => Rapat::TIPE_LOKASI]);
    }

    public function store(StoreRapatRequest $request): RedirectResponse
    {
        $rapat = new Rapat($request->validated());
        $rapat->slug   = (Str::slug($rapat->judul_rapat) ?: 'rapat') . '-' . Str::lower(Str::random(6));
        $rapat->status = 'aktif';
        $rapat->save();

        return redirect()
            ->route('admin.rapat.show', $rapat->id)
            ->with('success', 'Rapat berhasil dibuat. Bagikan link presensi kepada peserta.');
    }

    /** Detail rapat + daftar peserta */
    public function show(int $id): View
    {
        $rapat = Rapat::withCount('absensis')->findOrFail($id);

        // Kolom tanda_tangan (base64) sengaja tidak dimuat di daftar agar halaman ringan;
        // gambarnya diambil per baris lewat route admin.absensi.ttd.
        $absensis = $rapat->absensis()
            ->select(['id', 'rapat_id', 'nama_lengkap', 'jabatan', 'unit_kerja', 'waktu_hadir'])
            ->orderBy('waktu_hadir')
            ->paginate(25);

        return view('admin.show', compact('rapat', 'absensis'));
    }

    /** Menyajikan gambar tanda tangan (hanya untuk admin yang login) */
    /** Menyajikan gambar tanda tangan (hanya untuk admin yang login) */
    public function tandaTangan(int $id): Response
    {
        $raw = Absensi::select(['id', 'tanda_tangan'])->findOrFail($id)->tanda_tangan;

        if (blank($raw)) {
            abort(404);
        }

        $pos = strpos($raw, ',');
        if ($pos === false || ! preg_match('#^data:(image/(?:png|jpeg));base64$#', substr($raw, 0, $pos), $m)) {
            abort(404);
        }

        $binary = base64_decode(substr($raw, $pos + 1), true);
        if ($binary === false) {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type'           => $m[1],
            'Cache-Control'          => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Unduh rekap kehadiran dalam PDF */
    public function exportPdf(int $id)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        $rapat = Rapat::with(['absensis' => fn ($q) => $q->orderBy('waktu_hadir')])->findOrFail($id);

        $pdf = Pdf::loadView('admin.pdf_rekap', [
            'rapat'       => $rapat,
            'absensis'    => $rapat->absensis,
            'dicetakOleh' => Auth::user()->name,
            'dicetakPada' => now(),
        ])->setPaper('a4', 'portrait');

        // Render dulu, lalu tambahkan nomor halaman pada canvas
        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font   = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
        $canvas->page_text(
            $canvas->get_width() - 120,
            $canvas->get_height() - 28,
            'Halaman {PAGE_NUM} dari {PAGE_COUNT}',
            $font, 8, [0.4, 0.4, 0.4]
        );

        $judul = Str::of($rapat->judul_rapat)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '-')
            ->trim('-')
            ->limit(80, '')
            ->toString();

        return $pdf->download('Rekap-Absensi-' . ($judul ?: 'Rapat') . '.pdf');
    }

}