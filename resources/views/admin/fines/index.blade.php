@extends('layouts.app')

@section('title', 'Data Denda')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Denda</li>
        </ol>
    </nav>
    <h3 class="mb-3">Data Denda</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Anggota</th>
                        <th>Buku</th>
                        <th>Alasan</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fines as $fine)
                        <tr>
                            <td>{{ $fine->loan->user->name }}</td>
                            <td>{{ $fine->loan->book->title }}</td>
                            <td>{{ $fine->reason }}</td>
                            <td>Rp{{ number_format((float) $fine->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $fine->status === 'paid' ? 'success' : 'danger' }}">
                                    {{ $fine->status === 'paid' ? 'Lunas' : 'Belum lunas' }}
                                </span>
                            </td>
                            <td>
                                @if ($fine->status === 'unpaid')
                                    <form action="{{ route('fines.pay', $fine) }}" method="POST"
                                        onsubmit="return confirm('Konfirmasi pembayaran denda ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Tandai Lunas</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data denda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $fines->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
