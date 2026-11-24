@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
    @include('admin._nav')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mx-auto" style="max-width: 620px;">

        {{-- Data pengguna --}}
        <form method="POST" action="{{ route('admin.pengguna.update', $pengguna) }}"
              class="card border-0 shadow-sm mb-3" style="border-radius: 1rem;">
            @csrf
            @method('PUT')
            <div class="card-body p-4">
                <h1 class="h5 fw-bold mb-4"><i class="bi bi-person-gear me-2"></i>Ubah Pengguna</h1>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" maxlength="255" value="{{ old('name', $pengguna->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" maxlength="255" value="{{ old('email', $pengguna->email) }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Peran</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
                            @disabled($diriSendiri)>
                        <option value="admin" @selected(old('role', $pengguna->role) === 'admin')>Admin: operasional</option>
                        <option value="super_admin" @selected(old('role', $pengguna->role) === 'super_admin')>Super Admin: akses penuh</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <input type="hidden" name="aktif" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="aktif" name="aktif" value="1"
                               @checked(old('aktif', $pengguna->aktif)) @disabled($diriSendiri)>
                        <label class="form-check-label" for="aktif">Akun aktif</label>
                    </div>
                    <div class="form-text">Akun nonaktif tidak bisa login dan sesinya langsung dikeluarkan.</div>
                </div>

                @if ($diriSendiri)
                    <div class="alert alert-info small">
                        Ini akun Anda sendiri. Peran dan status tidak dapat diubah di sini. Untuk mengganti password Anda,
                        gunakan menu <strong>Ubah Password</strong>.
                    </div>
                @endif

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        {{-- Atur ulang password (bukan untuk akun sendiri) --}}
        @unless ($diriSendiri)
            <form method="POST" action="{{ route('admin.pengguna.password', $pengguna) }}"
                  class="card border-0 shadow-sm" style="border-radius: 1rem;">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-key me-2"></i>Atur Ulang Password</h2>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password sementara baru</label>
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
                            Sesi aktif pengguna dikeluarkan, dan ia wajib menggantinya saat login berikutnya.
                            Berikan password ini secara langsung kepadanya.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-outline-danger">Atur Ulang Password</button>
                    </div>
                </div>
            </form>
        @endunless
    </div>

    @unless ($diriSendiri)
        @include('admin.pengguna._pwgen')
    @endunless
@endsection