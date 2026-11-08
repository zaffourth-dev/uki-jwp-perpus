@extends('layouts.app')

@section('title', 'Data Anggota')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Anggota</li>
        </ol>
    </nav>
    <h3 class="mb-3">Data Anggota</h3>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Daftar Anggota</span>
            <a href="/users/create" class="btn btn-primary btn-sm">Tambah Anggota</a>
        </div>
        <form action="{{ route('admin.user.index') }}" method="GET" class="row g-2 p-3 border-bottom">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="search" name="search" class="form-control"
                        placeholder="Cari nama atau email anggota" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </div>
            @if (request()->filled('search'))
                <div class="col-auto">
                    <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            @endif
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'success' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->locale('id')->translatedFormat('l, d F Y') }}</td>
                            <td>
                                <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.user.destroy', $user) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if ($users->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Anggota tidak ditemukan.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
