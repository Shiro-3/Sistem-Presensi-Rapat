@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    @include('admin._nav')
    @php($bolehHapus = auth()->user()->can('hapus-rapat'))

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['Total Rapat', $stats['total'], 'bi-calendar3', 'primary'],
            ['Rapat Aktif', $stats['aktif'], 'bi-broadcast', 'success'],
            ['Rapat Selesai', $stats['selesai'], 'bi-check2-circle', 'secondary'],
            ['Total Kehadiran', $stats['hadir'], 'bi-people-fill', 'info'],
        ] as [$label, $nilai, $ikon, $warna])
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="rounded-3 bg-{{ $warna }}-subtle text-{{ $warna }} d-grid place-items-center p-2 fs-4">
                            <i class="bi {{ $ikon }}"></i>
                        </span>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ number_format($nilai, 0, ',', '.') }}</div>
                            <small class="text-secondary">{{ $label }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pencarian & filter --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 mb-3">
        <div class="col-12 col-md">
            <input type="search" name="q" value="{{ $search }}" class="form-control"
                   placeholder="Cari judul rapat…">
        </div>
        <div class="col-8 col-md-3">
            <select name="status" class="form-select">
                @foreach ($filterOptions as $key => $label)
                    <option value="{{ $key }}" @selected($filter === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-auto">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Terapkan</button>
        </div>
    </form>

    {{-- Bar aksi massal (muncul saat ada rapat yang dipilih) --}}
    @if ($bolehHapus)
    <div id="barBulk" class="alert alert-danger py-2 mb-3" hidden>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span><i class="bi bi-check2-square me-1"></i><strong id="jumlahDipilih">0</strong> rapat dipilih</span>
            <div class="d-flex gap-2">
                <button type="button" id="btnBatalPilih" class="btn btn-sm btn-outline-secondary">Batal pilih</button>
                <button type="button" id="btnHapusTerpilih" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash me-1"></i>Hapus terpilih
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel rapat --}}
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @if ($bolehHapus)
                        <th style="width:40px">
                            <input type="checkbox" class="form-check-input" id="cekSemua" aria-label="Pilih semua rapat di halaman ini">
                        </th>
                        @endif
                        <th>Rapat</th>
                        <th>Jadwal</th>
                        <th>Lokasi</th>
                        <th class="text-center">Hadir</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rapats as $rapat)
                    <tr>
                        @if ($bolehHapus)
                        <td>
                            <input type="checkbox" class="form-check-input cek-rapat" value="{{ $rapat->id }}"
                                   data-judul="{{ $rapat->judul_rapat }}"
                                   aria-label="Pilih {{ $rapat->judul_rapat }}">
                        </td>
                        @endif
                        <td style="min-width: 220px;">
                            <a href="{{ route('admin.rapat.show', $rapat->id) }}" class="fw-semibold text-decoration-none">
                                {{ $rapat->judul_rapat }}
                            </a>
                        </td>
                        <td class="small text-nowrap">
                            {{ $rapat->tanggal_rapat->translatedFormat('d M Y') }}<br>
                            <span class="text-secondary">{{ substr($rapat->jam_mulai, 0, 5) }} – {{ substr($rapat->jam_selesai, 0, 5) }}</span>
                        </td>
                        <td class="small">{{ $rapat->tipe_lokasi }}</td>
                        <td class="text-center fw-semibold">{{ $rapat->absensis_count }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $rapat->isAktif() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $rapat->status_label }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.rapat.show', $rapat->id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.rapat.pdf', $rapat->id) }}" class="btn btn-sm btn-outline-danger" title="Unduh PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @if ($bolehHapus)
                            <button type="button" class="btn btn-sm btn-danger btn-hapus" title="Hapus rapat"
                                    data-url="{{ route('admin.rapat.destroy', $rapat->id) }}"
                                    data-judul="{{ $rapat->judul_rapat }}"
                                    data-hadir="{{ $rapat->absensis_count }}">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $bolehHapus ? 7 : 6 }}" class="text-center text-secondary py-5">Belum ada rapat.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $rapats->links('pagination::bootstrap-5') }}
    </div>

    {{-- Form penghapusan (dipakai bersama untuk hapus satu dan hapus massal) --}}
    @if ($bolehHapus)
    <form method="POST" id="formHapus" action="">
        @csrf
        @method('DELETE')
        <input type="hidden" name="q" value="{{ $search }}">
        <input type="hidden" name="status" value="{{ $filter }}">
        <div id="idsWrap"></div>
    </form>

    {{-- Modal konfirmasi --}}
    <div class="modal fade" id="hapusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-6 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="hapusJudul">Hapus rapat?</span>
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                @endif
                <div class="modal-body">
                    <div id="hapusIsi"></div>
                    <p class="small text-secondary mb-0 mt-2">
                        Seluruh data kehadiran peserta pada rapat tersebut, termasuk tanda tangan dan foto,
                        ikut terhapus permanen dan tidak dapat dikembalikan.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formHapus" class="btn btn-danger" id="btnKonfirmasiHapus">
                        <i class="bi bi-trash me-1"></i>Ya, hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($bolehHapus)
@push('scripts')
<script>
(() => {
    const modal      = new bootstrap.Modal(document.getElementById('hapusModal'));
    const form       = document.getElementById('formHapus');
    const idsWrap    = document.getElementById('idsWrap');
    const judulEl    = document.getElementById('hapusJudul');
    const isiEl      = document.getElementById('hapusIsi');
    const btnOk      = document.getElementById('btnKonfirmasiHapus');
    const bar        = document.getElementById('barBulk');
    const jumlahEl   = document.getElementById('jumlahDipilih');
    const cekSemua   = document.getElementById('cekSemua');
    const cekList    = [...document.querySelectorAll('.cek-rapat')];
    const urlMassal  = @json(route('admin.rapat.destroy-many'));

    // Semua teks dari data rapat dimasukkan lewat textContent agar aman dari XSS
    const para = (teks) => {
        const p = document.createElement('p');
        p.className = 'mb-1';
        p.textContent = teks;
        return p;
    };

    /* ---------- Hapus satu rapat ---------- */
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', () => {
            const hadir = Number(btn.dataset.hadir);

            form.action = btn.dataset.url;
            idsWrap.replaceChildren();
            judulEl.textContent = 'Hapus rapat ini?';
            isiEl.replaceChildren(
                para('"' + btn.dataset.judul + '"'),
                para(hadir > 0
                    ? 'Rapat ini memiliki ' + hadir + ' data kehadiran.'
                    : 'Rapat ini belum memiliki data kehadiran.')
            );
            btnOk.disabled = false;
            modal.show();
        });
    });

    /* ---------- Pilih beberapa rapat ---------- */
    function perbarui() {
        const dipilih = cekList.filter(c => c.checked);
        jumlahEl.textContent = dipilih.length;
        bar.hidden = dipilih.length === 0;
        cekSemua.checked = dipilih.length > 0 && dipilih.length === cekList.length;
        cekSemua.indeterminate = dipilih.length > 0 && dipilih.length < cekList.length;
    }

    cekSemua.addEventListener('change', () => {
        cekList.forEach(c => { c.checked = cekSemua.checked; });
        perbarui();
    });
    cekList.forEach(c => c.addEventListener('change', perbarui));

    document.getElementById('btnBatalPilih').addEventListener('click', () => {
        cekList.forEach(c => { c.checked = false; });
        perbarui();
    });

    /* ---------- Hapus beberapa rapat ---------- */
    document.getElementById('btnHapusTerpilih').addEventListener('click', () => {
        const dipilih = cekList.filter(c => c.checked);
        if (!dipilih.length) return;

        form.action = urlMassal;
        idsWrap.replaceChildren(...dipilih.map(c => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = c.value;
            return input;
        }));

        judulEl.textContent = 'Hapus ' + dipilih.length + ' rapat terpilih?';

        const daftar = document.createElement('ul');
        daftar.className = 'mb-1 ps-3';
        dipilih.slice(0, 5).forEach(c => {
            const li = document.createElement('li');
            li.textContent = c.dataset.judul;
            daftar.appendChild(li);
        });
        if (dipilih.length > 5) {
            const li = document.createElement('li');
            li.textContent = 'dan ' + (dipilih.length - 5) + ' rapat lainnya';
            daftar.appendChild(li);
        }

        isiEl.replaceChildren(daftar);
        btnOk.disabled = false;
        modal.show();
    });

    // Cegah klik ganda saat penghapusan dikirim
    form.addEventListener('submit', () => { btnOk.disabled = true; });

    perbarui();
})();
</script>
@endpush
@endif