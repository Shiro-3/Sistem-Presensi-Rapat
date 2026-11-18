@extends('layouts.app')

@section('title', 'Presensi: ' . $rapat->judul_rapat)

@push('styles')
<style>
    .ttd-wrap { position: relative; border: 2px dashed #adb5bd; border-radius: .75rem; background: #fff; overflow: hidden; }
    .ttd-wrap.is-invalid { border-color: #dc3545; }
    #ttd-canvas { display: block; width: 100%; height: 220px; touch-action: none; cursor: crosshair; }
    .ttd-hint { position: absolute; inset: 0; display: grid; place-items: center; pointer-events: none;
        color: #adb5bd; font-size: .95rem; }
</style>
@endpush

@section('content')
<div class="mx-auto" style="max-width: 620px;">

    <a href="{{ route('rapat.index') }}" class="text-decoration-none small d-inline-block mb-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar rapat
    </a>

    {{-- Ringkasan rapat --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 1rem;">
        <div class="card-body">
            <span class="badge rounded-pill text-bg-success mb-2">
                <i class="bi bi-circle-fill me-1" style="font-size:.45rem"></i>{{ $rapat->status_label }}
            </span>
            <h1 class="h5 fw-bold mb-1">{{ $rapat->judul_rapat }}</h1>
            

            <div class="row g-2 small">
                <div class="col-12 col-sm-6"><i class="bi bi-calendar-event me-2 text-primary"></i>{{ $rapat->tanggal_rapat->translatedFormat('l, d F Y') }}</div>
                <div class="col-12 col-sm-6"><i class="bi bi-clock me-2 text-primary"></i>{{ substr($rapat->jam_mulai, 0, 5) }} – {{ substr($rapat->jam_selesai, 0, 5) }} WIB</div>
                <div class="col-12"><i class="bi bi-geo-alt me-2 text-primary"></i><strong>{{ $rapat->tipe_lokasi }}</strong>: {!! nl2br(e($rapat->detail_lokasi)) !!}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        {{-- Status sukses --}}
        <div class="card border-0 shadow-sm text-center" style="border-radius: 1rem;">
            <div class="card-body py-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                <h2 class="h5 fw-bold mt-3">Presensi Berhasil</h2>
                <p class="text-secondary mb-4">{{ session('success') }}</p>
                <a href="{{ route('rapat.index') }}" class="btn btn-primary">Kembali ke Daftar Rapat</a>
            </div>
        </div>
    @else
        {{-- Form presensi --}}
        <form method="POST" action="{{ route('rapat.presensi.store', $rapat->slug) }}"
              id="form-presensi" class="card border-0 shadow-sm" style="border-radius: 1rem;" novalidate>
            @csrf
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-person-vcard me-2"></i>Data Peserta</h2>

                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" maxlength="255"
                           class="form-control form-control-lg @error('nama_lengkap') is-invalid @enderror"
                           value="{{ old('nama_lengkap') }}" autocomplete="name" required>
                    @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="nip" class="form-label">NIP<span class="text-danger">*</span></label>
                    <input type="text" id="nip" name="nip" maxlength="50" inputmode="numeric"
                           class="form-control form-control-lg @error('nip') is-invalid @enderror"
                           value="{{ old('nip') }}" autocomplete="off">
                    @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <input type="text" id="jabatan" name="jabatan" maxlength="255"
                           class="form-control form-control-lg @error('jabatan') is-invalid @enderror"
                           value="{{ old('jabatan') }}" autocomplete="organization-title" required>
                    @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="unit_kerja" class="form-label">Unit Kerja <span class="text-danger">*</span></label>
                    <input type="text" id="unit_kerja" name="unit_kerja" maxlength="255"
                           class="form-control form-control-lg @error('unit_kerja') is-invalid @enderror"
                           value="{{ old('unit_kerja') }}" autocomplete="organization" required>
                    @error('unit_kerja') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <h2 class="h6 fw-bold mb-2"><i class="bi bi-pen me-2"></i>Tanda Tangan <span class="text-danger">*</span></h2>

                <div class="ttd-wrap @error('tanda_tangan') is-invalid @enderror" id="ttdWrap">
                    <canvas id="ttd-canvas"></canvas>
                    <div class="ttd-hint" id="ttdHint">Tanda tangan di sini</div>
                </div>
                <div class="text-danger small mt-1 {{ $errors->has('tanda_tangan') ? '' : 'd-none' }}" id="ttdError">
                    {{ $errors->first('tanda_tangan') ?: 'Tanda tangan wajib diisi.' }}
                </div>
                <input type="hidden" name="tanda_tangan" id="tanda_tangan">

                <button type="button" id="ttd-hapus" class="btn btn-outline-danger btn-sm mt-2">
                    <i class="bi bi-eraser me-1"></i>Hapus TTD
                </button>

                <div class="d-grid mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="spinner" aria-hidden="true"></span>
                        <span id="btnLabel"><i class="bi bi-send me-1"></i>Kirim Presensi</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@if (! session('success'))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
<script>
(() => {
    const form    = document.getElementById('form-presensi');
    const canvas  = document.getElementById('ttd-canvas');
    const wrap    = document.getElementById('ttdWrap');
    const hint    = document.getElementById('ttdHint');
    const errBox  = document.getElementById('ttdError');
    const hidden  = document.getElementById('tanda_tangan');
    const btn     = document.getElementById('btnSubmit');

    // Latar putih agar PNG tidak transparan (terbaca di semua tema)
    const pad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)',
        penColor: 'rgb(20,20,60)',
        minWidth: 0.8,
        maxWidth: 2.4,
    });

    // Sesuaikan resolusi canvas. Hanya dijalankan jika lebar berubah, karena di HP
    // event resize juga terpicu saat address bar tampil/hilang dan akan menghapus coretan.
    let lastWidth = 0;
    function resizeCanvas() {
        if (canvas.offsetWidth === lastWidth) return;
        lastWidth = canvas.offsetWidth;

        const data  = pad.toData();
        const ratio = Math.min(Math.max(window.devicePixelRatio || 1, 1), 2); // batasi 2x agar ukuran base64 wajar
        canvas.width  = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
        pad.fromData(data);
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Sembunyikan placeholder & pesan error ketika mulai menulis
    pad.addEventListener('beginStroke', () => {
        hint.classList.add('d-none');
        wrap.classList.remove('is-invalid');
        errBox.classList.add('d-none');
    });

    // Tombol Hapus TTD
    document.getElementById('ttd-hapus').addEventListener('click', () => {
        pad.clear();
        hidden.value = '';
        hint.classList.remove('d-none');
    });

    // Submit: ubah coretan menjadi Base64 lalu masukkan ke input tersembunyi
    form.addEventListener('submit', (e) => {
        if (pad.isEmpty()) {
            e.preventDefault();
            wrap.classList.add('is-invalid');
            errBox.textContent = 'Tanda tangan wajib diisi.';
            errBox.classList.remove('d-none');
            wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
            showToast('Tanda tangan wajib diisi', 'danger');
            return;
        }

        if (!form.checkValidity()) {          // validasi bawaan browser untuk field wajib
            e.preventDefault();
            form.reportValidity();
            return;
        }

        hidden.value = pad.toDataURL('image/png');

        // Cegah klik ganda
        btn.disabled = true;
        document.getElementById('spinner').classList.remove('d-none');
        document.getElementById('btnLabel').textContent = 'Mengirim…';
    });
})();
</script>
@endpush
@endif