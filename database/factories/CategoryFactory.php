<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }

    /**
     * Indicate that the category is the seeded web programming category.
     */
    public function webProgramming(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Pemrograman Web',
            'slug' => 'pemrograman-web',
        ]);
    }

    /**
     * Indicate that the category is the seeded database category.
     */
    public function database(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Basis Data',
            'slug' => 'basis-data',
        ]);
    }
}
