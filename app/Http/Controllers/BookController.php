<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'stock' => 'nullable|in:available,out',
        ]);

        $books = Book::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('publisher', 'like', "%{$search}%");
                });
            })
            ->when(($filters['stock'] ?? null) === 'available', fn ($query) => $query->where('stock', '>', 0))
            ->when(($filters['stock'] ?? null) === 'out', fn ($query) => $query->where('stock', 0))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return view('admin.book.index', compact('books'));
    }

    public function create()
    {
        return view('admin.book.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'author' => 'required|string|max:100',
            'publisher' => 'required|string|max:100',
            'stock' => 'required|integer|min:0',
        ]);

        Book::create($validated);

        return redirect()->route('admin.book.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    // ==========================================
    // TAMBAHKAN METHOD BARU DI BAWAH INI:
    // ==========================================

    /**
     * Menampilkan formulir untuk mengedit buku tertentu.
     */
    public function edit(string $id)
    {
        $book = Book::findOrFail($id);

        return view('admin.book.edit', compact('book'));
    }

    /**
     * Memperbarui data buku tertentu di database.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            "title" => "required|string|max:150",
            "author" => "required|string|max:100",
            "publisher" => "required|string|max:100",
            "stock" => "required|integer|min:0"
        ]);

        $book = Book::findOrFail($id);
        $book->update($validated);

        return redirect()->route("admin.book.index")
            ->with("success", "Data buku berhasil diperbarui.");
    }

        /**
     * Menghapus data buku tertentu dari database.
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);

        if ($book->loans()->exists()) {
            return redirect()->route("admin.book.index")
                ->with("error", "Buku tidak dapat dihapus karena memiliki riwayat peminjaman.");
        }

        $book->delete();

        return redirect()->route("admin.book.index")
            ->with("success", "Buku berhasil dihapus.");
    }
}
