<?php

namespace Tests\Feature\Admin;

use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_member_routes(): void
    {
        $this->get(route('admin.members.index'))
            ->assertRedirect(route('login'));
    }

    public function test_anggota_cannot_access_member_management(): void
    {
        $anggota = User::factory()->create();

        $this->actingAs($anggota)
            ->get(route('admin.members.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_member_index(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->create([
            'member_code' => 'AGT-1234',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee('AGT-1234')
            ->assertSee($member->user->email);
    }

    public function test_admin_can_create_member_user_and_profile_together(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Nabila Anggota',
            'email' => 'nabila@perpus.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'member_code' => 'AGT-0042',
            'phone' => '081200000042',
            'address' => 'Purwokerto Selatan',
        ]);

        $response->assertRedirect(route('admin.members.index', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'nabila@perpus.test',
            'role' => UserRole::Anggota->value,
        ]);
        $this->assertDatabaseHas('members', [
            'member_code' => 'AGT-0042',
            'phone' => '081200000042',
        ]);
    }

    public function test_admin_can_update_member_and_account_data(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->create([
            'member_code' => 'AGT-0043',
            'phone' => '081200000043',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.members.update', $member), [
            'name' => 'Member Diperbarui',
            'email' => 'member-baru@perpus.test',
            'password' => '',
            'password_confirmation' => '',
            'member_code' => 'AGT-9001',
            'phone' => '081299999999',
            'address' => 'Purwokerto Timur',
        ]);

        $response->assertRedirect(route('admin.members.index', absolute: false));
        $this->assertDatabaseHas('users', [
            'id' => $member->user->getKey(),
            'name' => 'Member Diperbarui',
            'email' => 'member-baru@perpus.test',
        ]);
        $this->assertDatabaseHas('members', [
            'id' => $member->getKey(),
            'member_code' => 'AGT-9001',
            'phone' => '081299999999',
        ]);
    }

    public function test_admin_can_delete_member_without_borrowing_history(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->create();
        $userId = $member->user->getKey();

        $response = $this->actingAs($admin)->delete(route('admin.members.destroy', $member));

        $response->assertRedirect(route('admin.members.index', absolute: false));
        $this->assertDatabaseMissing('members', [
            'id' => $member->getKey(),
        ]);
        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    public function test_admin_cannot_delete_member_with_borrowing_history(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->create();
        Borrowing::factory()->for($member)->create();

        $response = $this->actingAs($admin)->delete(route('admin.members.destroy', $member));

        $response->assertRedirect(route('admin.members.index', absolute: false));
        $response->assertSessionHas('error');
        $this->assertModelExists($member);
    }
}
