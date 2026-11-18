@extends('layouts.app')

@section('title', 'Tambah Rapat')

@section('content')
    @include('admin._nav')

    <div class="mx-auto" style="max-width: 680px;">
        <form method="POST" action="{{ route('admin.rapat.store') }}" class="card border-0 shadow-sm" style="border-radius: 1rem;">
            @csrf
            <div class="card-body p-4">
                <h1 class="h5 fw-bold mb-4"><i class="bi bi-calendar-plus me-2"></i>Jadwal Rapat Baru</h1>

                <div class="mb-3">
                    <label for="judul_rapat" class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                    <input type="text" id="judul_rapat" name="judul_rapat" maxlength="255" value="{{ old('judul_rapat') }}"
                           class="form-control @error('judul_rapat') is-invalid @enderror" required>
                    @error('judul_rapat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="penyelenggara" class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                    <input type="text" id="penyelenggara" name="penyelenggara" maxlength="255" value="{{ old('penyelenggara') }}"
                           class="form-control @error('penyelenggara') is-invalid @enderror" required>
                    @error('penyelenggara') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label for="tanggal_rapat" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_rapat" name="tanggal_rapat" value="{{ old('tanggal_rapat', now()->toDateString()) }}"
                               class="form-control @error('tanggal_rapat') is-invalid @enderror" required>
                        @error('tanggal_rapat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai') }}"
                               class="form-control @error('jam_mulai') is-invalid @enderror" required>
                        @error('jam_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai') }}"
                               class="form-control @error('jam_selesai') is-invalid @enderror" required>
                        @error('jam_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Tipe Lokasi <span class="text-danger">*</span></label>
                    <div class="btn-group w-100 flex-wrap" role="group">
                        @foreach ($tipeLokasi as $i => $tipe)
                            <input type="radio" class="btn-check" name="tipe_lokasi" id="tipe{{ $i }}"
                                   value="{{ $tipe }}" @checked(old('tipe_lokasi', 'Luring') === $tipe)>
                            <label class="btn btn-outline-primary" for="tipe{{ $i }}">{{ $tipe }}</label>
                        @endforeach
                    </div>
                    @error('tipe_lokasi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="detail_lokasi" class="form-label">Detail Lokasi <span class="text-danger">*</span></label>
                    <textarea id="detail_lokasi" name="detail_lokasi" rows="3" maxlength="1000"
                              class="form-control @error('detail_lokasi') is-invalid @enderror" required>{{ old('detail_lokasi') }}</textarea>
                    <div class="form-text" id="lokasiHelp"></div>
                    @error('detail_lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Simpan Rapat</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const hints = {
        'Daring': 'Isi tautan rapat, misalnya link Zoom/Google Meet beserta ID dan passcode.',
        'Luring': 'Isi alamat atau nama ruangan, misalnya "Ruang Rapat Lt. 2, Gedung A".',
        'Daring & Luring': 'Isi nama ruangan sekaligus tautan rapat daringnya.',
    };
    const help = document.getElementById('lokasiHelp');
    const update = () => {
        const checked = document.querySelector('input[name="tipe_lokasi"]:checked');
        help.textContent = checked ? hints[checked.value] || '' : '';
    };
    document.querySelectorAll('input[name="tipe_lokasi"]').forEach(r => r.addEventListener('change', update));
    update();
})();
</script>
@endpush