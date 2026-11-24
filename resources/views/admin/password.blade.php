@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
    @include('admin._nav')
    
    @if (session('peringatan'))
        <div class="alert alert-warning">{{ session('peringatan') }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mx-auto" style="max-width: 520px;">
        <form method="POST" action="{{ route('admin.password.update') }}"
              class="card border-0 shadow-sm" style="border-radius: 1rem;">
            @csrf
            @method('PUT')

            <div class="card-body p-4">
                <h1 class="h5 fw-bold mb-1"><i class="bi bi-key me-2"></i>Ubah Password</h1>
                <p class="text-secondary small mb-4">Akun: <strong>{{ auth()->user()->email }}</strong></p>

                @foreach ([
                    ['password_lama', 'Password saat ini', 'current-password'],
                    ['password', 'Password baru', 'new-password'],
                    ['password_confirmation', 'Ulangi password baru', 'new-password'],
                ] as [$nama, $label, $auto])
                    <div class="mb-3">
                        <label for="{{ $nama }}" class="form-label">{{ $label }}</label>
                        <div class="input-group">
                            <input type="password" id="{{ $nama }}" name="{{ $nama }}"
                                   autocomplete="{{ $auto }}" required
                                   class="form-control form-control-lg @error($nama) is-invalid @enderror">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw"
                                    data-target="{{ $nama }}" aria-label="Tampilkan password">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error($nama) <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endforeach

                <div class="form-text mb-4">
                    Minimal 10 karakter, memuat huruf besar, huruf kecil, dan angka.
                    Setelah password diganti, perangkat lain yang sedang login dengan akun ini akan dikeluarkan.
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Simpan Password
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
        const input  = document.getElementById(btn.dataset.target);
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        btn.querySelector('i').className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
        btn.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
    });
});
</script>
@endpush