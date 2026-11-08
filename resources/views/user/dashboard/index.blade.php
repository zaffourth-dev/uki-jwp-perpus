@extends('layouts.app')

@section('title', 'Dashboard Anggota')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Beranda</li>
        </ol>
    </nav>
    <h3 class="mb-3">Halo, {{ auth()->user()->name }}</h3>
    @if ($unpaidFineTotal > 0)
        <div class="alert alert-warning">
            Total denda belum lunas: <strong>Rp{{ number_format((float) $unpaidFineTotal, 0, ',', '.') }}</strong>.
            <a href="{{ route('fines.index') }}">Lihat denda</a>
        </div>
    @endif
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm text-white bg-primary h-100">
                <div class="card-body">
                    <div>Pinjaman Aktif</div>
                    <div class="display-6 fw-bold">{{ $activeLoanCount }}</div>
                    <a href="{{ route('loans.index') }}" class="btn btn-light btn-sm mt-2">Lihat peminjaman</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm text-dark bg-warning h-100">
                <div class="card-body">
                    <div>Denda Belum Lunas</div>
                    <div class="display-6 fw-bold">Rp{{ number_format((float) $unpaidFineTotal, 0, ',', '.') }}</div>
                    <a href="{{ route('fines.index') }}" class="btn btn-light btn-sm mt-2">Lihat denda</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm text-white bg-success h-100">
                <div class="card-body">
                    <div>Judul Buku Tersedia</div>
                    <div class="display-6 fw-bold">{{ $availableBookCount }}</div>
                    <a href="{{ route('catalog.index') }}" class="btn btn-light btn-sm mt-2">Lihat katalog</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Peminjaman Saya</span>
            <a href="{{ route('loans.index') }}" class="btn btn-outline-primary btn-sm">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLoans as $loan)
                        <tr>
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
                            <td colspan="4" class="text-center text-muted py-4">Belum ada peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
