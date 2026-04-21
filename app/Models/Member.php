<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['user_id', 'member_code', 'phone', 'address'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class)->active();
    }

    public function borrowingItems(): HasManyThrough
    {
        return $this->hasManyThrough(BorrowingItem::class, Borrowing::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('member_code');
    }

    public static function nextMemberCode(): string
    {
        $takenMemberCodes = self::query()->pluck('member_code');
        $sequence = 1;

        do {
            $memberCode = 'AGT-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($takenMemberCodes->contains($memberCode));

        return $memberCode;
    }
}
