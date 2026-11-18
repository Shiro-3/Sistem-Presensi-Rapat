@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<div class="mx-auto" style="max-width: 420px;">
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold mb-1"><i class="bi bi-person-lock me-2"></i>Login Admin</h1>
            <p class="text-secondary small mb-4">Masuk untuk mengelola rapat dan rekap kehadiran.</p>

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           autocomplete="username" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control form-control-lg" autocomplete="current-password" required>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection