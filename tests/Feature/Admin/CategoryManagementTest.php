<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_category_routes(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_anggota_cannot_access_category_management(): void
    {
        $anggota = User::factory()->create();

        $this->actingAs($anggota)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_category_index(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create([
            'name' => 'Majalah',
            'slug' => 'majalah',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Majalah')
            ->assertSee('majalah');
    }

    public function test_admin_can_create_a_category_with_auto_generated_slug(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Teknik Industri',
            'slug' => '',
        ]);

        $response->assertRedirect(route('admin.categories.index', absolute: false));
        $this->assertDatabaseHas('categories', [
            'name' => 'Teknik Industri',
            'slug' => 'teknik-industri',
        ]);
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create([
            'name' => 'Sains',
            'slug' => 'sains',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Sains Terapan',
            'slug' => 'sains-terapan',
        ]);

        $response->assertRedirect(route('admin.categories.index', absolute: false));
        $this->assertDatabaseHas('categories', [
            'id' => $category->getKey(),
            'name' => 'Sains Terapan',
            'slug' => 'sains-terapan',
        ]);
    }

    public function test_admin_cannot_delete_a_category_that_is_still_used_by_books(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Book::factory()->for($category)->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index', absolute: false));
        $response->assertSessionHas('error');
        $this->assertModelExists($category);
    }

    public function test_admin_can_delete_an_unused_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index', absolute: false));
        $this->assertModelMissing($category);
    }
}
