@extends('layouts.app')

@section('title', 'Peminjaman')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Peminjaman</li>
        </ol>
    </nav>
    <h3 class="mb-3">Data Peminjaman</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Daftar Peminjaman</span>
            <a href="{{ route('loans.create') }}" class="btn btn-primary btn-sm">Catat Peminjaman</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Anggota</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td>{{ $loan->user->name }}</td>
                            <td>{{ $loan->book->title }}</td>
                            <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                            <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                            <td>{{ $loan->return_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if ($loan->status === 'borrowed')
                                    <span class="badge bg-primary">Dipinjam</span>
                                @else
                                    <span class="badge bg-success">Dikembalikan</span>
                                @endif
                            </td>
                            <td>
                                @if ($loan->status === 'borrowed')
                                    <form action="{{ route('loans.return', $loan) }}" method="POST"
                                        onsubmit="return confirm('Catat pengembalian buku ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Kembalikan</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $loans->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
