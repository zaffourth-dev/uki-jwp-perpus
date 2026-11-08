<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_members_and_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Anggota Contoh',
            'email' => 'contoh@example.test',
        ]);

        $this->actingAs($admin)->get(route('admin.user.index'))
            ->assertOk()
            ->assertSee('Anggota Contoh');
        $this->actingAs($admin)->get(route('admin.user.create'))
            ->assertOk()
            ->assertSee('Simpan Anggota');
    }

    public function test_admin_can_create_member_with_hashed_password_and_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.user.store'), [
            'name' => 'Anggota Baru',
            'email' => 'baru@example.test',
            'password' => 'kata-sandi-rahasia',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.user.index'))
            ->assertSessionHas('success', 'Anggota berhasil ditambahkan.');
        $member = User::where('email', 'baru@example.test')->firstOrFail();
        $this->assertSame('user', $member->role);
        $this->assertTrue(Hash::check('kata-sandi-rahasia', $member->password));
    }

    public function test_admin_can_update_a_member_and_leave_password_unchanged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $originalPassword = $member->password;

        $response = $this->actingAs($admin)->put(route('admin.user.update', $member), [
            'name' => 'Nama Baru',
            'email' => 'anggota-baru@example.test',
            'password' => '',
        ]);

        $response->assertRedirect(route('admin.user.index'));
        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Nama Baru',
            'email' => 'anggota-baru@example.test',
            'password' => $originalPassword,
        ]);
    }

    public function test_admin_can_change_member_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.user.update', $member), [
            'name' => $member->name,
            'email' => $member->email,
            'password' => 'password-baru',
        ]);

        $response->assertRedirect(route('admin.user.index'));
        $member->refresh();
        $this->assertTrue(Hash::check('password-baru', $member->password));
    }

    public function test_admin_can_delete_member_without_loan_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.user.destroy', $member));

        $response->assertRedirect(route('admin.user.index'));
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_member_with_loan_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $book = Book::create([
            'title' => 'Buku Tes',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'stock' => 1,
        ]);
        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.user.destroy', $member));

        $response->assertRedirect(route('admin.user.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $member->id]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete(route('admin.user.destroy', $admin));

        $response->assertRedirect(route('admin.user.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_search_members_by_name_or_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Nadia Pustaka',
            'email' => 'nadia@example.test',
        ]);
        User::factory()->create([
            'name' => 'Budi Anggota',
            'email' => 'budi@example.test',
        ]);

        $response = $this->actingAs($admin)->get('/users?search=nadia@example.test');

        $response->assertOk()
            ->assertSee('Nadia Pustaka')
            ->assertDontSee('Budi Anggota')
            ->assertSee('value="nadia@example.test"', false);
    }

    public function test_member_search_shows_empty_state_when_no_results_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/users?search=tidak-ada');

        $response->assertOk()
            ->assertSee('Anggota tidak ditemukan.');
    }
}
