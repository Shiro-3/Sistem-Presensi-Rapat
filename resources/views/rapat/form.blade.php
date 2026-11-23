@extends('layouts.app')

@section('title', 'Presensi: ' . $rapat->judul_rapat)

@push('styles')
<style>
    .ttd-wrap { position: relative; border: 2px dashed #adb5bd; border-radius: .75rem; background: #fff; overflow: hidden; }
    .ttd-wrap.is-invalid { border-color: #dc3545; }
    #ttd-canvas { display: block; width: 100%; height: 220px; touch-action: none; cursor: crosshair; }
    .ttd-hint { position: absolute; inset: 0; display: grid; place-items: center; pointer-events: none;
        color: #adb5bd; font-size: .95rem; }

    .foto-box { position: relative; border: 2px dashed #adb5bd; border-radius: .75rem; background: #000;
        overflow: hidden; aspect-ratio: 4 / 3; }
    .foto-box.is-invalid { border-color: #dc3545; }
    .foto-box video, .foto-box img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .foto-hint { position: absolute; inset: 0; display: grid; place-items: center; text-align: center;
        padding: 1rem; background: #f8f9fa; color: #adb5bd; font-size: .95rem; }
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
            <h1 class="h5 fw-bold mb-3">{{ $rapat->judul_rapat }}</h1>

            <div class="row g-2 small">
                <div class="col-12 col-sm-6"><i class="bi bi-calendar-event me-2 text-primary"></i>{{ $rapat->tanggal_rapat->translatedFormat('l, d F Y') }}</div>
                <div class="col-12 col-sm-6"><i class="bi bi-clock me-2 text-primary"></i>{{ substr($rapat->jam_mulai, 0, 5) }} – {{ substr($rapat->jam_selesai, 0, 5) }} WIB</div>
                <div class="col-12"><i class="bi bi-geo-alt me-2 text-primary"></i><strong>{{ $rapat->tipe_lokasi }}</strong>: {!! nl2br(e($rapat->detail_lokasi)) !!}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="card border-0 shadow-sm text-center" style="border-radius: 1rem;">
            <div class="card-body py-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                <h2 class="h5 fw-bold mt-3">Presensi Berhasil</h2>
                <p class="text-secondary mb-4">{{ session('success') }}</p>
                <a href="{{ route('rapat.index') }}" class="btn btn-primary">Kembali ke Daftar Rapat</a>
            </div>
        </div>
    @else
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
                    <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <input type="text" id="jabatan" name="jabatan" maxlength="255"
                           class="form-control form-control-lg @error('jabatan') is-invalid @enderror"
                           value="{{ old('jabatan') }}" autocomplete="organization-title" required>
                    @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="{{ $rapat->minta_email ? 'mb-3' : 'mb-4' }}">
                    <label for="unit_kerja" class="form-label">Unit Kerja <span class="text-danger">*</span></label>
                    <input type="text" id="unit_kerja" name="unit_kerja" maxlength="255"
                           class="form-control form-control-lg @error('unit_kerja') is-invalid @enderror"
                           value="{{ old('unit_kerja') }}" autocomplete="organization" required>
                    @error('unit_kerja') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if ($rapat->minta_email)
                <div class="mb-4">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" maxlength="255"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" autocomplete="email" inputmode="email" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif

                {{-- Tanda tangan (selalu ada) --}}
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

                {{-- Foto bukti (hanya jika diminta admin) --}}
                @if ($rapat->minta_foto)
                    <h2 class="h6 fw-bold mt-4 mb-2"><i class="bi bi-camera me-2"></i>Foto Bukti Kehadiran <span class="text-danger">*</span></h2>

                    <div class="foto-box @error('foto') is-invalid @enderror" id="fotoBox">
                        <video id="fotoVideo" autoplay playsinline muted class="d-none"></video>
                        <img id="fotoPreview" alt="Pratinjau foto" class="d-none">
                        <div class="foto-hint" id="fotoHint">Tekan &quot;Buka Kamera&quot; untuk mengambil foto</div>
                    </div>
                    <div class="text-danger small mt-1 {{ $errors->has('foto') ? '' : 'd-none' }}" id="fotoError">
                        {{ $errors->first('foto') ?: 'Foto bukti wajib diambil.' }}
                    </div>
                    <input type="hidden" name="foto" id="foto">

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" id="btnBukaKamera" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-camera-video me-1"></i>Buka Kamera
                        </button>
                        <button type="button" id="btnAmbilFoto" class="btn btn-primary btn-sm d-none">
                            <i class="bi bi-camera me-1"></i>Ambil Foto
                        </button>
                        <button type="button" id="btnUlangiFoto" class="btn btn-outline-danger btn-sm d-none">
                            <i class="bi bi-arrow-repeat me-1"></i>Ulangi
                        </button>
                        <label for="fotoFile" class="btn btn-outline-secondary btn-sm mb-0">
                            <i class="bi bi-phone me-1"></i>Ambil dari perangkat
                        </label>
                        <input type="file" id="fotoFile" accept="image/*" capture="user" class="d-none">
                    </div>
                    <div class="form-text">Foto hanya dapat dilihat oleh admin dan dipakai sebagai bukti kehadiran.</div>
                @endif

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

    /* ================= Tanda tangan ================= */
    const pad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)',
        penColor: 'rgb(20,20,60)',
        minWidth: 0.8,
        maxWidth: 2.4,
    });

    // Hanya sesuaikan resolusi jika lebar berubah (di HP, event resize juga terpicu saat address bar bergeser)
    let lastWidth = 0;
    function resizeCanvas() {
        if (canvas.offsetWidth === lastWidth) return;
        lastWidth = canvas.offsetWidth;

        const data  = pad.toData();
        const ratio = Math.min(Math.max(window.devicePixelRatio || 1, 1), 2);
        canvas.width  = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
        pad.fromData(data);
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    pad.addEventListener('beginStroke', () => {
        hint.classList.add('d-none');
        wrap.classList.remove('is-invalid');
        errBox.classList.add('d-none');
    });

    document.getElementById('ttd-hapus').addEventListener('click', () => {
        pad.clear();
        hidden.value = '';
        hint.classList.remove('d-none');
    });

    /* ================= Foto bukti (hanya jika diminta admin) ================= */
    const butuhFoto = {{ $rapat->minta_foto ? 'true' : 'false' }};
    const fotoBox   = document.getElementById('fotoBox');
    const fotoErr   = document.getElementById('fotoError');
    const fotoInput = document.getElementById('foto');

    if (butuhFoto) {
        const video    = document.getElementById('fotoVideo');
        const preview  = document.getElementById('fotoPreview');
        const fotoHint = document.getElementById('fotoHint');
        const btnBuka  = document.getElementById('btnBukaKamera');
        const btnAmbil = document.getElementById('btnAmbilFoto');
        const btnUlang = document.getElementById('btnUlangiFoto');
        const fileIn   = document.getElementById('fotoFile');
        let stream = null;

        // state: 'idle' (belum ada foto) | 'live' (kamera menyala) | 'done' (foto sudah diambil)
        function setState(s) {
            video.classList.toggle('d-none', s !== 'live');
            preview.classList.toggle('d-none', s !== 'done');
            fotoHint.classList.toggle('d-none', s !== 'idle');
            btnBuka.classList.toggle('d-none', s !== 'idle');
            btnAmbil.classList.toggle('d-none', s !== 'live');
            btnUlang.classList.toggle('d-none', s !== 'done');
        }

        function stopKamera() {
            if (stream) stream.getTracks().forEach(t => t.stop());
            stream = null;
            video.srcObject = null;
        }

        async function bukaKamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showToast('Kamera tidak didukung di sini. Gunakan "Ambil dari perangkat".', 'danger');
                return;
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } },
                    audio: false,
                });
                video.srcObject = stream;
                setState('live');
            } catch (e) {
                showToast('Kamera tidak dapat dibuka. Izinkan akses kamera atau gunakan "Ambil dari perangkat".', 'danger');
            }
        }

        // Kecilkan gambar (maks. 720 px) dan ubah ke JPEG agar ukurannya ringan
        function keJpeg(sumber, w, h) {
            const skala = Math.min(1, 720 / Math.max(w, h));
            const c = document.createElement('canvas');
            c.width  = Math.round(w * skala);
            c.height = Math.round(h * skala);
            c.getContext('2d').drawImage(sumber, 0, 0, c.width, c.height);
            return c.toDataURL('image/jpeg', 0.65);
        }

        function simpanFoto(dataUrl) {
            fotoInput.value = dataUrl;
            preview.src = dataUrl;
            fotoBox.classList.remove('is-invalid');
            fotoErr.classList.add('d-none');
            setState('done');
        }

        btnBuka.addEventListener('click', bukaKamera);

        btnAmbil.addEventListener('click', () => {
            if (!video.videoWidth) return;
            simpanFoto(keJpeg(video, video.videoWidth, video.videoHeight));
            stopKamera();
        });

        btnUlang.addEventListener('click', () => {
            fotoInput.value = '';
            preview.removeAttribute('src');
            bukaKamera();
        });

        // Cadangan: ambil/pilih foto lewat perangkat (di HP akan membuka kamera)
        fileIn.addEventListener('change', () => {
            const file = fileIn.files[0];
            fileIn.value = '';
            if (!file) return;

            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                simpanFoto(keJpeg(img, img.naturalWidth, img.naturalHeight));
                URL.revokeObjectURL(url);
                stopKamera();
            };
            img.onerror = () => {
                URL.revokeObjectURL(url);
                showToast('File bukan gambar yang valid.', 'danger');
            };
            img.src = url;
        });

        window.addEventListener('pagehide', stopKamera);
        setState('idle');
    }

    /* ================= Submit ================= */
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

        if (butuhFoto && !fotoInput.value) {
            e.preventDefault();
            fotoBox.classList.add('is-invalid');
            fotoErr.textContent = 'Foto bukti wajib diambil.';
            fotoErr.classList.remove('d-none');
            fotoBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            showToast('Foto bukti wajib diambil', 'danger');
            return;
        }

        if (!form.checkValidity()) {
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