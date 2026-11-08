@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Beranda</li>
        </ol>
    </nav>
    <h3 class="mb-3">Dashboard</h3>
    <div class="alert alert-info">Selamat datang, {{ auth()->user()->name }}.</div>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-white bg-primary h-100">
                <div class="card-body">
                    <div>Total Buku</div>
                    <div class="display-6 fw-bold">{{ $bookCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-white bg-success h-100">
                <div class="card-body">
                    <div>Anggota</div>
                    <div class="display-6 fw-bold">{{ $memberCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-dark bg-warning h-100">
                <div class="card-body">
                    <div>Peminjaman Aktif</div>
                    <div class="display-6 fw-bold">{{ $activeLoanCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-white bg-danger h-100">
                <div class="card-body">
                    <div>Denda Belum Lunas</div>
                    <div class="display-6 fw-bold">{{ $unpaidFineCount }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Peminjaman Terbaru</span>
            <a href="{{ route('loans.index') }}" class="btn btn-outline-primary btn-sm">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Anggota</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLoans as $loan)
                        <tr>
                            <td>{{ $loan->user->name }}</td>
                            <td>{{ $loan->book->title }}</td>
                            <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                            <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $loan->status === 'borrowed' ? 'primary' : 'success' }}">
                                    {{ $loan->status === 'borrowed' ? 'Dipinjam' : 'Dikembalikan' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
