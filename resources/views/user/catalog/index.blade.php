@extends('layouts.app')

@section('title', 'Katalog Buku')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Katalog Buku</li>
        </ol>
    </nav>
    <h3 class="mb-3">Katalog Buku</h3>
    <div class="card shadow-sm">
        <form action="{{ route('catalog.index') }}" method="GET" class="row g-2 p-3 border-bottom">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="search" name="search" class="form-control"
                        placeholder="Cari judul, penulis, atau penerbit" value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </div>
            @if (request()->filled('search'))
                <div class="col-auto">
                    <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author }}</td>
                            <td>{{ $book->publisher }}</td>
                            <td>{{ $book->stock > 0 ? $book->stock . ' tersedia' : 'Habis' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Buku tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $books->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
