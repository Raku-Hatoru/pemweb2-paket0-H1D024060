<?php

namespace App\Models;

use App\BorrowingStatus;
use Carbon\CarbonInterface;
use Database\Factories\BorrowingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'member_id',
    'borrow_date',
    'due_date',
    'return_date',
    'status',
    'total_fine',
    'notes',
])]
class Borrowing extends Model
{
    /** @use HasFactory<BorrowingFactory> */
    use HasFactory;

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function borrowingItems(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'borrowing_items')
            ->withPivot('id', 'qty')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BorrowingStatus::Dipinjam->value,
            BorrowingStatus::Terlambat->value,
        ]);
    }

    public function isReturned(): bool
    {
        return $this->return_date !== null || $this->status === BorrowingStatus::Dikembalikan;
    }

    public function canBeReturned(): bool
    {
        return ! $this->isReturned();
    }

    public function displayStatus(?CarbonInterface $referenceDate = null): BorrowingStatus
    {
        if ($this->isReturned()) {
            return BorrowingStatus::Dikembalikan;
        }

        $currentDate = $referenceDate ?? now();

        if ($currentDate->greaterThan($this->due_date)) {
            return BorrowingStatus::Terlambat;
        }

        return BorrowingStatus::Dipinjam;
    }

    public function lateDaysFor(CarbonInterface $returnDate): int
    {
        if ($returnDate->lessThanOrEqualTo($this->due_date)) {
            return 0;
        }

        return $this->due_date->diffInDays($returnDate);
    }

    public function fineFor(CarbonInterface $returnDate): int
    {
        return $this->lateDaysFor($returnDate) * 1000;
    }

    public function resolvedStatusFor(CarbonInterface $returnDate): BorrowingStatus
    {
        return BorrowingStatus::Dikembalikan;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'status' => BorrowingStatus::class,
            'total_fine' => 'integer',
        ];
    }
}
