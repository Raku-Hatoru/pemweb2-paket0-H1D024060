<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure guests can view the registration screen.
     */
    public function test_guest_can_view_the_registration_screen(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Anggota Baru',
            'email' => 'anggota@perpus.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::query()->where('email', 'anggota@perpus.test')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('anggota.dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'name' => 'Anggota Baru',
            'email' => 'anggota@perpus.test',
            'role' => 'anggota',
        ]);
    }

    public function test_registration_requires_a_unique_email_address(): void
    {
        User::factory()->create([
            'email' => 'anggota@perpus.test',
        ]);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Anggota Baru',
            'email' => 'anggota@perpus.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Anggota Baru',
            'email' => 'anggota@perpus.test',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
    }
}
