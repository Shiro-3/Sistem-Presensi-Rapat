@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
    @include('admin._nav')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-bold mb-0">Kelola Pengguna</h1>
            <p class="text-secondary small mb-0">Akun yang dapat masuk ke area admin.</p>
        </div>
        <a href="{{ route('admin.pengguna.create') }}"
           class="btn btn-primary d-inline-flex align-items-center justify-content-center">
            <i class="bi bi-person-plus me-1"></i>Tambah Pengguna
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($penggunas as $u)
                    <tr>
                        <td class="fw-semibold">
                            {{ $u->name }}
                            @if ($u->is(auth()->user()))
                                <span class="badge text-bg-light border fw-normal">Anda</span>
                            @endif
                        </td>
                        <td class="text-break">{{ $u->email }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $u->isSuperAdmin() ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                {{ $u->labelPeran() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill {{ $u->aktif ? 'text-bg-success' : 'text-bg-danger' }}">
                                {{ $u->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if ($u->wajib_ganti_password)
                                <span class="badge rounded-pill text-bg-warning">Belum ganti password</span>
                            @endif
                        </td>
                        <td class="small text-nowrap">{{ $u->created_at?->translatedFormat('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.pengguna.edit', $u) }}"
                               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-pencil me-1"></i>Ubah
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection