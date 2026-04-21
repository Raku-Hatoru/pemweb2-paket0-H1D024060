<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_login_screen(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('anggota.dashboard', absolute: false));
    }

    public function test_admin_is_redirected_to_the_admin_dashboard_after_login(): void
    {
        $user = User::factory()->admin()->create([
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password-salah',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_dashboard_redirects_admins_to_the_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_dashboard_redirects_anggota_to_the_anggota_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('anggota.dashboard', absolute: false));
    }

    public function test_admin_cannot_access_the_anggota_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('anggota.dashboard'));

        $response->assertForbidden();
    }

    public function test_anggota_cannot_access_the_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_guests_are_redirected_from_role_protected_dashboards(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('anggota.dashboard'))
            ->assertRedirect(route('login'));
    }
}
