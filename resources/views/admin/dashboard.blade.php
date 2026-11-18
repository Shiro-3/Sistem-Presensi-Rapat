@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    @include('admin._nav')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    {{-- Tabel rapat --}}
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
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
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-5">Belum ada rapat.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $rapats->links('pagination::bootstrap-5') }}
    </div>
@endsection