<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'member_code' => fake()->unique()->numerify('AGT-####'),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
        ];
    }

    /**
     * Indicate that the member is the seeded demo member.
     */
    public function demo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'member_code' => 'AGT-0001',
            'phone' => '081234567890',
            'address' => 'Purwokerto',
        ]);
    }
}
