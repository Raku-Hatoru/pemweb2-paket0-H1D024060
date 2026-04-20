<?php

namespace App\Models;

use App\BorrowingStatus;
use Database\Factories\BorrowingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
