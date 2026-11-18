@extends('layouts.app')

@section('title', $rapat->judul_rapat)

@push('styles')
<style>
    .ttd-thumb { height: 40px; width: auto; background: #fff; border: 1px solid #dee2e6;
        border-radius: .375rem; padding: 2px; cursor: zoom-in; }
</style>
@endpush

@section('content')
    @include('admin._nav')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Info rapat --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 1rem;">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <span class="badge rounded-pill {{ $rapat->isAktif() ? 'text-bg-success' : 'text-bg-secondary' }} mb-2">
                        {{ $rapat->status_label }}
                    </span>
                    <h1 class="h5 fw-bold mb-1">{{ $rapat->judul_rapat }}</h1>
                </div>
                <div class="d-flex flex-wrap gap-2 align-self-start">
                    <button type="button" class="btn btn-outline-secondary" id="btnSalin"
                            data-url="{{ route('rapat.form', $rapat->slug) }}">
                        <i class="bi bi-link-45deg me-1"></i>Salin Link Presensi
                    </button>
                    <a href="{{ route('admin.rapat.pdf', $rapat->id) }}" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Unduh PDF
                    </a>
                </div>
            </div>

            <hr>

            <div class="row g-2 small">
                <div class="col-12 col-md-4"><i class="bi bi-calendar-event me-2 text-primary"></i>{{ $rapat->tanggal_rapat->translatedFormat('l, d F Y') }}</div>
                <div class="col-12 col-md-4"><i class="bi bi-clock me-2 text-primary"></i>{{ substr($rapat->jam_mulai, 0, 5) }} – {{ substr($rapat->jam_selesai, 0, 5) }} WIB</div>
                <div class="col-12 col-md-4"><i class="bi bi-people-fill me-2 text-primary"></i><strong>{{ $rapat->absensis_count }}</strong> peserta hadir</div>
                <div class="col-12"><i class="bi bi-geo-alt me-2 text-primary"></i><strong>{{ $rapat->tipe_lokasi }}</strong>: {!! nl2br(e($rapat->detail_lokasi)) !!}</div>
            </div>
        </div>
    </div>

    {{-- Daftar peserta --}}
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="card-header bg-white fw-bold" style="border-radius: 1rem 1rem 0 0;">Daftar Peserta Hadir</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Unit Kerja</th>
                        <th>Waktu Hadir</th>
                        <th>Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($absensis as $a)
                    <tr>
                        <td>{{ $absensis->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $a->nama_lengkap }}</td>
                        <td>{{ $a->nip ?: '-' }}</td>
                        <td>{{ $a->jabatan }}</td>
                        <td>{{ $a->unit_kerja }}</td>
                        <td class="small text-nowrap">
                            {{ $a->waktu_hadir->translatedFormat('d M Y') }}<br>
                            <span class="text-secondary">{{ $a->waktu_hadir->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <img src="{{ route('admin.absensi.ttd', $a->id) }}" loading="lazy"
                                 class="ttd-thumb" alt="Tanda tangan {{ $a->nama_lengkap }}"
                                 data-nama="{{ $a->nama_lengkap }}">
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-5">Belum ada peserta yang hadir.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $absensis->links('pagination::bootstrap-5') }}
    </div>

    {{-- Modal pratinjau tanda tangan --}}
    <div class="modal fade" id="ttdModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-6" id="ttdModalTitle">Tanda Tangan</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="ttdModalImg" src="" alt="Tanda tangan" class="img-fluid border rounded bg-white">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    // Pratinjau tanda tangan
    const modalEl = document.getElementById('ttdModal');
    const modal   = new bootstrap.Modal(modalEl);
    document.querySelectorAll('.ttd-thumb').forEach(img => {
        img.addEventListener('click', () => {
            document.getElementById('ttdModalImg').src = img.src;
            document.getElementById('ttdModalTitle').textContent = 'Tanda tangan: ' + img.dataset.nama;
            modal.show();
        });
    });

    // Salin link presensi
    document.getElementById('btnSalin').addEventListener('click', async (e) => {
        const url = e.currentTarget.dataset.url;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                showToast('Link presensi disalin ke clipboard');
                return;
            }
        } catch (err) { /* lanjut ke fallback */ }
        window.prompt('Salin link presensi (Ctrl+C):', url);
    });
})();
</script>
@endpush