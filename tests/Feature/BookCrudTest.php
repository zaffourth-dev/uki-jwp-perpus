<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_book_list_and_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Book::create([
            'title' => 'Buku Contoh',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'stock' => 2,
        ]);

        $this->actingAs($admin)->get(route('admin.book.index'))
            ->assertOk()
            ->assertSee('Buku Contoh');
        $this->actingAs($admin)->get('/books/create')
            ->assertOk()
            ->assertSee('Simpan Buku');
    }

    public function test_admin_can_update_a_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $book = Book::create([
            'title' => 'Judul Lama',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'stock' => 2,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.book.update', $book), [
            'title' => 'Judul Baru',
            'author' => 'Penulis Baru',
            'publisher' => 'Penerbit Baru',
            'stock' => 4,
        ]);

        $response->assertRedirect(route('admin.book.index'))
            ->assertSessionHas('success', 'Data buku berhasil diperbarui.');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Judul Baru',
            'author' => 'Penulis Baru',
            'publisher' => 'Penerbit Baru',
            'stock' => 4,
        ]);
    }

    public function test_admin_can_delete_book_without_loan_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $book = Book::create([
            'title' => 'Buku Hapus',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'stock' => 2,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.book.destroy', $book));

        $response->assertRedirect(route('admin.book.index'))
            ->assertSessionHas('success', 'Buku berhasil dihapus.');
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_admin_cannot_delete_book_with_loan_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $book = Book::create([
            'title' => 'Buku Dipinjam',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'stock' => 0,
        ]);
        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.book.destroy', $book));

        $response->assertRedirect(route('admin.book.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }
}
