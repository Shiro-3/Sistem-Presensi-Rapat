<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <a href="{{ route('admin.dashboard') }}"
       class="btn btn-sm d-inline-flex align-items-center justify-content-center {{ request()->routeIs('admin.dashboard') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-house-door"></i> Dashboard
    </a>
    <a href="{{ route('admin.rapat.create') }}"
       class="btn btn-sm d-inline-flex align-items-center justify-content-center {{ request()->routeIs('admin.rapat.create') ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-plus-circle me-1"></i>Tambah Rapat
    </a>
    <a href="{{ route('rapat.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center">
        <i class="bi bi-collection"></i> Katalog Rapat
    </a>
    <form method="POST" action="{{ route('logout') }}" class="ms-auto">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-arrow-right me-1"></i>Keluar
        </button>
    </form>
</div>