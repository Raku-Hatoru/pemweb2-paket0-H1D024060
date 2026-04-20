<?php

namespace Database\Factories;

use App\BorrowingStatus;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Borrowing>
 */
class BorrowingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $borrowDate = Carbon::instance(fake()->dateTimeBetween('-7 days', 'now'));

        return [
            'member_id' => Member::factory(),
            'borrow_date' => $borrowDate->toDateString(),
            'due_date' => $borrowDate->copy()->addDays(7)->toDateString(),
            'return_date' => null,
            'status' => BorrowingStatus::Dipinjam,
            'total_fine' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function returned(): static
    {
        return $this->state(function (array $attributes): array {
            $dueDate = Carbon::parse($attributes['due_date']);
            $returnDate = $dueDate->copy()->addDays(2);

            return [
                'return_date' => $returnDate->toDateString(),
                'status' => BorrowingStatus::Dikembalikan,
                'total_fine' => 2000,
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(function (): array {
            $borrowDate = Carbon::now()->subDays(10);

            return [
                'borrow_date' => $borrowDate->toDateString(),
                'due_date' => $borrowDate->copy()->addDays(7)->toDateString(),
                'return_date' => null,
                'status' => BorrowingStatus::Terlambat,
                'total_fine' => 3000,
            ];
        });
    }
}
