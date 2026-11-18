@extends('layouts.app')

@section('title', 'Presensi Ditutup')

@section('content')
<div class="mx-auto text-center py-5" style="max-width: 480px;">
    <i class="bi bi-lock-fill text-secondary" style="font-size: 3.5rem;"></i>
    <h1 class="h4 fw-bold mt-3">Presensi Ditutup</h1>
    <p class="text-secondary">
        Rapat "<strong>{{ $rapat->judul_rapat }}</strong>" telah selesai atau tidak lagi menerima presensi.
    </p>
    <a href="{{ route('rapat.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Rapat
    </a>
</div>
@endsection