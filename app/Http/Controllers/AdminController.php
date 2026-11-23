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
use Illuminate\Support\Facades\DB;

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
        $rapat->slug       = (Str::slug($rapat->judul_rapat) ?: 'rapat') . '-' . Str::lower(Str::random(6));
        $rapat->status     = 'aktif';
        $rapat->minta_foto  = $request->boolean('minta_foto') ? 1 : 0;
        $rapat->minta_email = $request->boolean('minta_email') ? 1 : 0;
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
            ->select(['id', 'rapat_id', 'nama_lengkap', 'jabatan', 'unit_kerja', 'email', 'waktu_hadir'])
            ->orderBy('waktu_hadir')
            ->paginate(25);

        return view('admin.show', compact('rapat', 'absensis'));
    }

    /** Menyajikan gambar tanda tangan (hanya untuk admin yang login) */
    /** Menyajikan gambar tanda tangan (hanya untuk admin yang login) */
    /** Menyajikan gambar tanda tangan (hanya untuk admin yang login) */
    public function tandaTangan(int $id): Response
    {
        return $this->gambarResponse(Absensi::select(['id', 'tanda_tangan'])->findOrFail($id)->tanda_tangan);
    }

    /** Menyajikan foto bukti kehadiran (hanya untuk admin yang login) */
    public function foto(int $id): Response
    {
        return $this->gambarResponse(Absensi::select(['id', 'foto'])->findOrFail($id)->foto);
    }

    private function gambarResponse(?string $raw): Response
    {
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
    /** Ubah foto base64 menjadi thumbnail JPEG kecil khusus untuk PDF */
    private function fotoUntukPdf(?string $raw, int $tinggi = 160): ?string
    {
        if (blank($raw)) {
            return null;
        }

        $pos = strpos($raw, ',');
        if ($pos === false) {
            return null;
        }

        $binary = base64_decode(substr($raw, $pos + 1), true);
        if ($binary === false) {
            return null;
        }

        $img = @imagecreatefromstring($binary);
        if ($img === false) {
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($h > $tinggi) {
            $kecil = imagescale($img, (int) round($w * $tinggi / $h), $tinggi);
            if ($kecil !== false) {
                $img = $kecil;
            }
        }

        ob_start();
        imagejpeg($img, null, 70);
        $jpeg = ob_get_clean();

        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }

    /** Unduh rekap kehadiran dalam PDF */
    public function exportPdf(int $id)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        $rapat = Rapat::with(['absensis' => fn ($q) => $q->orderBy('waktu_hadir')])->findOrFail($id);

        // Foto bukti: kecilkan dulu agar PDF tidak membengkak
        if ($rapat->minta_foto) {
            foreach ($rapat->absensis as $absensi) {
                $absensi->foto = $this->fotoUntukPdf($absensi->foto);
            }
        }

        $pdf = Pdf::loadView('admin.pdf_rekap', [
            'rapat'       => $rapat,
            'absensis'    => $rapat->absensis,
            'instansi'    => config('instansi'),
            'logo'        => $this->logoDataUri(),
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
    
    /** Logo instansi sebagai data URI (lebih andal daripada path file di Windows) */
    private function logoDataUri(): ?string
    {
        $logo = config('instansi.logo');
        $path = $logo ? public_path($logo) : null;

        if (! $path || ! is_file($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default       => null,
        };

        return $mime ? 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path)) : null;
    }
    /** Hapus satu rapat beserta seluruh data kehadirannya */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $rapat = Rapat::findOrFail($id);

        DB::transaction(function () use ($rapat) {
            $rapat->absensis()->delete();
            $rapat->delete();
        });

        return $this->kembaliKeDashboard($request)
            ->with('success', "Rapat \"{$rapat->judul_rapat}\" berhasil dihapus.");
    }

    /** Hapus beberapa rapat sekaligus */
    public function destroyMany(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct'],
        ], [
            'ids.required' => 'Pilih minimal satu rapat yang akan dihapus.',
        ]);

        $jumlah = DB::transaction(function () use ($data) {
            Absensi::whereIn('rapat_id', $data['ids'])->delete();

            return Rapat::whereIn('id', $data['ids'])->delete();
        });

        return $this->kembaliKeDashboard($request)
            ->with('success', "{$jumlah} rapat berhasil dihapus.");
    }

    /** Kembali ke dashboard dengan pencarian/filter yang sedang aktif (halaman 1) */
    private function kembaliKeDashboard(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard', array_filter($request->only(['q', 'status'])));
    }

}