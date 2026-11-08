@extends('layouts.app')

@section('title', 'Catat Peminjaman')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Peminjaman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Catat Peminjaman</li>
        </ol>
    </nav>
    <h3 class="mb-3">Catat Peminjaman</h3>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Peminjaman Baru</div>
                <div class="card-body">
                    @if ($users->isEmpty() || $books->isEmpty())
                        <div class="alert alert-warning">
                            @if ($users->isEmpty())
                                Belum ada anggota yang dapat meminjam.
                            @else
                                Tidak ada buku dengan stok tersedia.
                            @endif
                        </div>
                    @endif
                    <form action="{{ route('loans.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Anggota</label>
                            <select id="user_id" name="user_id"
                                class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih anggota</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="book_id" class="form-label">Buku</label>
                            <select id="book_id" name="book_id"
                                class="form-select @error('book_id') is-invalid @enderror" required>
                                <option value="">Pilih buku</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>
                                        {{ $book->title }} (stok {{ $book->stock }})
                                    </option>
                                @endforeach
                            </select>
                            @error('book_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text mb-3">Masa pinjam 7 hari; maksimal 3 pinjaman aktif per anggota.</div>
                        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" @disabled($users->isEmpty() || $books->isEmpty())>
                            Simpan Peminjaman
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
