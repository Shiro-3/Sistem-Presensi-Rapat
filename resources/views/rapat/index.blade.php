@extends('layouts.app')

@section('title', 'Katalog Rapat')

@section('content')
    <section class="mb-3">
        <h1 class="h4 fw-bold mb-1">Katalog Rapat</h1>
        <p class="text-secondary small mb-0">Pilih rapat, lalu isi presensi dengan tanda tangan digital.</p>
    </section>

    {{-- Pencarian (live) --}}
    <div class="input-group input-group-lg mb-3 shadow-sm">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
        <input type="search" id="searchInput" class="form-control border-start-0 ps-0"
               placeholder="Cari judul atau penyelenggara…" value="{{ $search }}"
               autocomplete="off" aria-label="Cari rapat">
    </div>

    {{-- Filter tab --}}
    <ul class="nav flex-nowrap overflow-auto pb-2 mb-2 filter-tabs">
        @foreach ($filterOptions as $key => $label)
            <li class="nav-item flex-shrink-0">
                <a class="nav-link {{ $filter === $key ? 'active' : '' }}"
                   href="{{ route('rapat.index', array_filter([
                        'status' => $key === 'semua' ? null : $key,
                        'q'      => $search ?: null,
                   ])) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    {{-- Grid kartu --}}
    <div class="row g-3" id="rapatGrid">
        @forelse ($rapats as $rapat)
            @php
                $aktif = $rapat->isAktif();
                $ikon = match ($rapat->tipe_lokasi) {
                    'Daring' => 'bi-camera-video',
                    'Luring' => 'bi-geo-alt',
                    default  => 'bi-diagram-2',
                };
            @endphp
            <div class="col-12 col-md-6 col-lg-4 rapat-item"
                 data-search="{{ $rapat->judul_rapat }}">
                <div class="card rapat-card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge rounded-pill {{ $aktif ? 'text-bg-success' : 'text-bg-secondary' }}">
                                <i class="bi bi-circle-fill me-1" style="font-size:.45rem"></i>{{ $rapat->status_label }}
                            </span>
                            <span class="badge text-bg-light border fw-normal">
                                <i class="bi {{ $ikon }} me-1"></i>{{ $rapat->tipe_lokasi }}
                            </span>
                        </div>

                        <h2 class="h6 fw-bold mb-1">{{ $rapat->judul_rapat }}</h2>

                        <ul class="list-unstyled small mb-3">
                            <li class="mb-1"><i class="bi bi-calendar-event me-2 text-primary"></i>{{ $rapat->tanggal_rapat->translatedFormat('l, d F Y') }}</li>
                            <li class="mb-1"><i class="bi bi-clock me-2 text-primary"></i>{{ substr($rapat->jam_mulai, 0, 5) }} – {{ substr($rapat->jam_selesai, 0, 5) }} WIB</li>
                            <li><i class="bi bi-pin-map me-2 text-primary"></i>{{ \Illuminate\Support\Str::limit($rapat->detail_lokasi, 70) }}</li>
                        </ul>

                        <div class="mt-auto">
                            <div class="d-flex align-items-center small text-secondary mb-3">
                                <i class="bi bi-people-fill me-2"></i>
                                <span><strong class="text-body" data-count="{{ $rapat->slug }}">{{ $rapat->absensis_count }}</strong> peserta hadir</span>
                            </div>

                            <div class="d-flex gap-2">
                                @if ($aktif)
                                    <a href="{{ route('rapat.form', $rapat->slug) }}" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-pencil-square me-1"></i>Isi Presensi
                                    </a>
                                @else
                                    <button type="button" class="btn btn-secondary flex-grow-1" disabled>
                                        <i class="bi bi-lock-fill me-1"></i>Presensi Ditutup
                                    </button>
                                @endif

                                <button type="button" class="btn btn-outline-secondary btn-share"
                                        data-url="{{ route('rapat.form', $rapat->slug) }}"
                                        aria-label="Salin link presensi" title="Salin link presensi">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-secondary py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Belum ada rapat yang sesuai.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Hasil kosong dari live search --}}
    <div id="noResult" class="text-center text-secondary py-5 d-none">
        <i class="bi bi-search fs-1 d-block mb-2"></i>
        Tidak ada rapat yang cocok dengan pencarian.
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $rapats->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
<script>
(() => {
    /* ---------- Live search ---------- */
    const input  = document.getElementById('searchInput');
    const items  = [...document.querySelectorAll('.rapat-item')];
    const empty  = document.getElementById('noResult');
    const norm   = s => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    const index  = items.map(el => norm(el.dataset.search));

    function filterCards() {
        const q = norm(input.value);
        let visible = 0;
        items.forEach((el, i) => {
            const show = !q || index[i].includes(q);
            el.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        empty.classList.toggle('d-none', visible > 0 || items.length === 0);
    }
    input.addEventListener('input', filterCards);
    filterCards();

    /* ---------- Salin link (tombol Share) ---------- */
    async function copyText(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return true;
            }
        } catch (e) { /* lanjut ke fallback */ }

        // Fallback untuk koneksi non-HTTPS / browser lama
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (e) {}
        ta.remove();
        return ok;
    }

    document.querySelectorAll('.btn-share').forEach(btn => {
        btn.addEventListener('click', async () => {
            const ok = await copyText(btn.dataset.url);
            showToast(ok ? 'Link presensi disalin ke clipboard' : 'Gagal menyalin link', ok ? 'success' : 'danger');
        });
    });

    /* ---------- Live counter (polling tiap 15 detik) ---------- */
    const counters = [...document.querySelectorAll('[data-count]')];
    if (counters.length) {
        const refresh = async () => {
            if (document.hidden) return; // hemat request saat tab tidak aktif
            const params = new URLSearchParams();
            counters.forEach(c => params.append('slugs[]', c.dataset.count));
            try {
                const res  = await fetch(`{{ route('rapat.counts') }}?${params}`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                counters.forEach(c => {
                    const n = data[c.dataset.count];
                    if (n !== undefined && String(n) !== c.textContent) c.textContent = n;
                });
            } catch (e) { /* abaikan error jaringan sesaat */ }
        };
        setInterval(refresh, 15000);
    }
})();
</script>
@endpush