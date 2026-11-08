<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Buku | Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">Perpustakaan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="/books">Data Buku</a></li>
                    <li class="nav-item"><a class="nav-link" href="/users">Data Anggota</a></li>
                    <li class="nav-item"><a class="nav-link" href="/loans">Peminjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="/fines">Denda</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Nama Pengguna
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <h6 class="dropdown-header">Peran: Admin / Staff</h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="/logout" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4 flex-grow-1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data Buku</li>
            </ol>
        </nav>
        <h3 class="mb-3">Data Buku</h3>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Daftar Buku</span>
                <a href="/books/create" class="btn btn-primary btn-sm">Tambah Buku</a>
            </div>
            <form action="{{ route('admin.book.index') }}" method="GET" class="row g-2 p-3 border-bottom">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control"
                            placeholder="Cari judul, penulis, atau penerbit"
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">Cari</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="stock" class="form-select" aria-label="Filter stok">
                        <option value="">Semua stok</option>
                        <option value="available" @selected(request('stock') === 'available')>Tersedia</option>
                        <option value="out" @selected(request('stock') === 'out')>Habis</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Terapkan</button>
                </div>
                @if (request()->filled('search') || request()->filled('stock'))
                    <div class="col-auto">
                        <a href="{{ route('admin.book.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                @endif
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($books as $book)
                            <tr>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author }}</td>
                                <td>{{ $book->publisher }}</td>
                                <td>{{ $book->stock }}</td>
                                <td>
                                    <a href="{{ route('admin.book.edit', $book->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('admin.book.destroy', $book->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @if ($books->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada buku yang sesuai dengan pencarian atau filter.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $books->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </main>

    <footer class="bg-white border-top py-3 text-center text-muted small">
        Perpustakaan &copy; 2026
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
