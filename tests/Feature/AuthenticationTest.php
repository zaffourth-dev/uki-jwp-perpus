<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_an_admin_account(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@perpus.test')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(password_verify('password', $admin->password));
    }

    public function test_guest_is_redirected_to_login_from_dashboard_and_admin_pages(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('admin.book.index'))->assertRedirect(route('login'));
        $this->get(route('admin.user.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'anggota@example.test',
            'password' => 'password',
            'role' => 'user',
        ]);

        $this->post('/login', [
            'email' => 'anggota@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertOk()->assertViewIs('user.dashboard.index');

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_return_to_login_with_error(): void
    {
        $this->from(route('login'))->post('/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_regular_user_cannot_access_admin_pages(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.book.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.user.index'))->assertForbidden();
    }
}
