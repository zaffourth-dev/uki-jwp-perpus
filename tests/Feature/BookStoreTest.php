<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_a_book(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        $response = $this->actingAs($admin)->post('/books', [
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'publisher' => 'Bentang Pustaka',
            'stock' => 3,
        ]);

        $response->assertRedirect(route('admin.book.index'));
        $response->assertSessionHas('success', 'Buku berhasil ditambahkan.');
        $this->assertDatabaseHas('books', [
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'publisher' => 'Bentang Pustaka',
            'stock' => 3,
        ]);
    }

    public function test_book_is_not_stored_with_invalid_data(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        $response = $this->actingAs($admin)->from('/books/create')->post('/books', [
            'title' => '',
            'author' => 'Andrea Hirata',
            'publisher' => 'Bentang Pustaka',
            'stock' => -1,
        ]);

        $response->assertRedirect('/books/create');
        $response->assertSessionHasErrors(['title', 'stock']);
        $this->assertSame(0, Book::count());
    }

    public function test_admin_can_search_books_and_filter_by_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Book::create([
            'title' => 'Belajar Laravel',
            'author' => 'Ani',
            'publisher' => 'Pustaka A',
            'stock' => 2,
        ]);
        Book::create([
            'title' => 'Dasar PHP',
            'author' => 'Budi',
            'publisher' => 'Pustaka B',
            'stock' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/books?search=Laravel&stock=available');

        $response->assertOk()
            ->assertSee('Belajar Laravel')
            ->assertDontSee('Dasar PHP')
            ->assertSee('value="Laravel"', false)
            ->assertSee('value="available" selected', false);
    }

    public function test_admin_can_filter_books_with_no_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Book::create([
            'title' => 'Buku Tersedia',
            'author' => 'Ani',
            'publisher' => 'Pustaka A',
            'stock' => 1,
        ]);
        Book::create([
            'title' => 'Buku Habis',
            'author' => 'Budi',
            'publisher' => 'Pustaka B',
            'stock' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/books?stock=out');

        $response->assertOk()
            ->assertSee('Buku Habis')
            ->assertDontSee('Buku Tersedia');
    }
}
