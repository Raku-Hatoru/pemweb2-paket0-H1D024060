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
}
