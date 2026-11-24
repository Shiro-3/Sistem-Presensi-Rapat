@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    @include('admin._nav')

    <div class="mx-auto" style="max-width: 620px;">
        <form method="POST" action="{{ route('admin.pengguna.store') }}"
              class="card border-0 shadow-sm" style="border-radius: 1rem;">
            @csrf
            <div class="card-body p-4">
                <h1 class="h5 fw-bold mb-4"><i class="bi bi-person-plus me-2"></i>Tambah Pengguna</h1>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" maxlength="255" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email (untuk login) <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" maxlength="255" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Peran <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="admin" @selected(old('role', 'admin') === 'admin')>Admin: operasional (tambah rapat, unduh PDF)</option>
                        <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin: akses penuh</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password awal <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="password" name="password" autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        <button type="button" class="btn btn-outline-secondary btn-acak" data-target="password">
                            <i class="bi bi-magic me-1"></i>Acak
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-salin" data-target="password"
                                aria-label="Salin password">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">
                        Minimal 10 karakter, memuat huruf besar, huruf kecil, dan angka. Pengguna wajib menggantinya
                        saat login pertama, jadi berikan password ini secara langsung kepadanya.
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>

    @include('admin.pengguna._pwgen')
@endsection