<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'isbn' => fake()->unique()->numerify('978602######'),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'year_published' => fake()->numberBetween(2019, 2026),
            'stock' => fake()->numberBetween(1, 10),
            'cover_image' => null,
        ];
    }

    /**
     * Indicate that the book is the seeded Laravel demo title.
     */
    public function laravelInformationSystem(): static
    {
        return $this->state(fn (array $attributes): array => [
            'isbn' => '9786020000011',
            'title' => 'Laravel untuk Sistem Informasi',
            'author' => 'Tim Dosen Teknik',
            'publisher' => 'FT Press',
            'year_published' => 2024,
            'stock' => 5,
            'cover_image' => null,
        ]);
    }

    /**
     * Indicate that the book is the seeded database demo title.
     */
    public function libraryDatabaseDesign(): static
    {
        return $this->state(fn (array $attributes): array => [
            'isbn' => '9786020000012',
            'title' => 'Desain Basis Data Perpustakaan',
            'author' => 'Laboratorium Data',
            'publisher' => 'FT Press',
            'year_published' => 2023,
            'stock' => 3,
            'cover_image' => null,
        ]);
    }
}
