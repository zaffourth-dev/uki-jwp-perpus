<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanFineWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_loan_and_book_stock_is_decremented(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = $this->createBook(stock: 1);

        $response = $this->actingAs($admin)->post(route('loans.store'), [
            'user_id' => $member->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect(route('loans.index'))
            ->assertSessionHas('success', 'Peminjaman berhasil dicatat.');
        $this->assertDatabaseHas('loans', [
            'user_id' => $member->id,
            'book_id' => $book->id,
            'status' => 'borrowed',
        ]);
        $loan = Loan::firstOrFail();
        $this->assertSame(today()->toDateString(), $loan->loan_date->toDateString());
        $this->assertSame(today()->addDays(7)->toDateString(), $loan->due_date->toDateString());
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 0]);
    }

    public function test_admin_can_view_loan_form_and_administration_lists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createBook();
        User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->get(route('loans.create'))
            ->assertOk()
            ->assertSee('Simpan Peminjaman');
        $this->get(route('loans.index'))->assertOk()->assertSee('Data Peminjaman');
        $this->get(route('fines.index'))->assertOk()->assertSee('Data Denda');
    }

    public function test_admin_cannot_create_loan_for_out_of_stock_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = $this->createBook(stock: 0);

        $this->actingAs($admin)
            ->from(route('loans.create'))
            ->post(route('loans.store'), [
                'user_id' => $member->id,
                'book_id' => $book->id,
            ])
            ->assertRedirect(route('loans.create'))
            ->assertSessionHas('error', 'Stok buku sudah habis.');

        $this->assertDatabaseCount('loans', 0);
    }

    public function test_admin_cannot_exceed_active_loan_limit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = $this->createBook(stock: 1);

        foreach (range(1, 3) as $index) {
            $existingBook = $this->createBook(title: "Buku {$index}", stock: 0);
            Loan::create([
                'user_id' => $member->id,
                'book_id' => $existingBook->id,
                'loan_date' => today(),
                'due_date' => today()->addDays(7),
                'status' => 'borrowed',
            ]);
        }

        $this->actingAs($admin)
            ->from(route('loans.create'))
            ->post(route('loans.store'), [
                'user_id' => $member->id,
                'book_id' => $book->id,
            ])
            ->assertRedirect(route('loans.create'))
            ->assertSessionHas('error', 'Anggota sudah mencapai batas 3 pinjaman aktif.');

        $this->assertDatabaseCount('loans', 3);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 1]);
    }

    public function test_returning_book_increments_stock_and_creates_overdue_fine(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = $this->createBook(stock: 0);
        $loan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'loan_date' => today()->subDays(7),
            'due_date' => today()->addDays(2),
            'status' => 'borrowed',
        ]);

        $this->travel(4)->days();
        $response = $this->actingAs($admin)->post(route('loans.return', $loan));

        $response->assertRedirect(route('loans.index'))
            ->assertSessionHas('success', 'Buku berhasil dikembalikan. Denda keterlambatan telah dibuat.');
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'returned',
        ]);
        $this->assertSame(today()->toDateString(), $loan->fresh()->return_date->toDateString());
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 1]);
        $this->assertDatabaseHas('fines', [
            'loan_id' => $loan->id,
            'amount' => 2000,
            'reason' => 'Terlambat 2 hari',
            'status' => 'unpaid',
        ]);
    }

    public function test_on_time_return_does_not_create_fine_and_cannot_be_returned_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = $this->createBook(stock: 0);
        $loan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'loan_date' => today(),
            'due_date' => today()->addDays(7),
            'status' => 'borrowed',
        ]);

        $this->actingAs($admin)->post(route('loans.return', $loan))
            ->assertSessionHas('success', 'Buku berhasil dikembalikan.');
        $this->assertDatabaseCount('fines', 0);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 1]);

        $this->post(route('loans.return', $loan))
            ->assertSessionHas('error', 'Peminjaman ini sudah dikembalikan.');
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 1]);
    }

    public function test_member_sees_only_their_own_loans_fines_and_catalog(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $otherMember = User::factory()->create(['role' => 'user']);
        $book = $this->createBook(title: 'Buku Anggota');
        $otherBook = $this->createBook(title: 'Buku Anggota Lain');
        $loan = $this->createLoan($member, $book);
        $otherLoan = $this->createLoan($otherMember, $otherBook);
        Fine::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'reason' => 'Terlambat 1 hari',
            'status' => 'unpaid',
        ]);
        Fine::create([
            'loan_id' => $otherLoan->id,
            'amount' => 2000,
            'reason' => 'Terlambat 2 hari',
            'status' => 'unpaid',
        ]);

        $this->actingAs($member)->get(route('loans.index'))
            ->assertOk()
            ->assertSee('Buku Anggota')
            ->assertDontSee('Buku Anggota Lain');
        $this->get(route('fines.index'))
            ->assertOk()
            ->assertSee('Rp1.000')
            ->assertDontSee('Rp2.000');
        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Buku Anggota Lain');
        $this->get(route('loans.create'))->assertForbidden();
    }

    public function test_admin_can_mark_fine_paid_and_member_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $loan = $this->createLoan($member, $this->createBook());
        $fine = Fine::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'reason' => 'Terlambat 1 hari',
            'status' => 'unpaid',
        ]);

        $this->actingAs($member)->post(route('fines.pay', $fine))->assertForbidden();
        $this->assertDatabaseHas('fines', ['id' => $fine->id, 'status' => 'unpaid']);

        $this->actingAs($admin)->post(route('fines.pay', $fine))
            ->assertRedirect(route('fines.index'))
            ->assertSessionHas('success', 'Pembayaran denda berhasil dicatat.');
        $this->assertDatabaseHas('fines', ['id' => $fine->id, 'status' => 'paid']);
    }

    public function test_admin_dashboard_uses_live_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);
        $book = $this->createBook();
        $loan = $this->createLoan($member, $book);
        Fine::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'reason' => 'Terlambat 1 hari',
            'status' => 'unpaid',
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('admin.dashboard.index')
            ->assertViewHas('bookCount', 1)
            ->assertViewHas('memberCount', 1)
            ->assertViewHas('activeLoanCount', 1)
            ->assertViewHas('unpaidFineCount', 1);
    }

    public function test_member_dashboard_uses_only_their_own_live_data(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $otherMember = User::factory()->create(['role' => 'user']);
        $book = $this->createBook();
        $loan = $this->createLoan($member, $book);
        $otherLoan = $this->createLoan($otherMember, $this->createBook(title: 'Buku Lain'));
        Fine::create([
            'loan_id' => $loan->id,
            'amount' => 1500,
            'reason' => 'Terlambat',
            'status' => 'unpaid',
        ]);
        Fine::create([
            'loan_id' => $otherLoan->id,
            'amount' => 2500,
            'reason' => 'Terlambat',
            'status' => 'unpaid',
        ]);

        $this->actingAs($member)->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('user.dashboard.index')
            ->assertViewHas('activeLoanCount', 1)
            ->assertViewHas('unpaidFineTotal', 1500)
            ->assertViewHas('availableBookCount', 2);
    }

    private function createBook(string $title = 'Buku Uji', int $stock = 1): Book
    {
        return Book::create([
            'title' => $title,
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'stock' => $stock,
        ]);
    }

    private function createLoan(User $user, Book $book): Loan
    {
        return Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => today(),
            'due_date' => today()->addDays(7),
            'status' => 'borrowed',
        ]);
    }
}
