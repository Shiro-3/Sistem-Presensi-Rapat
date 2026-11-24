<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') | Sistem Absensi Rapat</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_PSDKP.png') }}?v=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <header class="navbar bg-white border-bottom sticky-top shadow-sm">
        <div class="container">
            <a href="{{ route('rapat.index') }}" class="navbar-brand d-flex align-items-center gap-2 m-0">
                <img src="{{ asset('images/logo_PSDKP.png') }}" alt="Logo" class="brand-logo-img">
                <span class="lh-1">
                    <span class="d-block fw-bold fs-6">Sistem Presensi Rapat Digital</span>
                    <small class="text-secondary fw-normal" style="font-size:.72rem">Pangkalan PSDKP Cilacap</small>
                </span>
            </a>
            <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-person-lock"></i>
                <span><span class="d-none d-sm-inline">Login </span>Admin</span>
            </a>
        </div>
    </header>

    <main class="container py-4">
        @yield('content')
    </main>

    <footer class="text-center text-secondary small py-3">
        &copy; {{ date('Y') }} Pangkalan PSDKP Cilacap
    </footer>

    <div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3" id="toastWrap"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toast global: showToast('Pesan', 'success' | 'danger' | 'dark')
        window.showToast = function (message, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            const el = document.createElement('div');
            el.className = `toast align-items-center text-bg-${type} border-0`;
            el.setAttribute('role', 'status');
            el.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>`;
            el.querySelector('.toast-body').textContent = message; // textContent: aman dari XSS
            wrap.appendChild(el);
            el.addEventListener('hidden.bs.toast', () => el.remove());
            new bootstrap.Toast(el, { delay: 2500 }).show();
        };
    </script>
    @stack('scripts')
</body>
</html>