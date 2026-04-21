<?php

namespace App\Http\Requests;

use App\Models\Book;
use App\Models\BorrowingItem;
use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBorrowingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Prepare the request data for validation.
     */
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function (mixed $item): array {
                $payload = is_array($item) ? $item : [];

                return [
                    'book_id' => $payload['book_id'] ?? null,
                    'qty' => $payload['qty'] ?? 1,
                ];
            })
            ->filter(fn (array $item): bool => filled($item['book_id']))
            ->values()
            ->all();

        $this->merge([
            'borrow_date' => (string) ($this->input('borrow_date') ?: now()->toDateString()),
            'notes' => filled($this->input('notes')) ? trim((string) $this->input('notes')) : null,
            'items' => $items,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'member_id' => ['required', Rule::exists(Member::class, 'id')],
            'borrow_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => [
                'required',
                'integer',
                'distinct:strict',
                Rule::exists(Book::class, 'id')->where(fn ($query) => $query->where('stock', '>', 0)),
            ],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:3'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $member = Member::query()->find($this->integer('member_id'));

                if (! $member) {
                    return;
                }

                /** @var Collection<int, array{book_id:int, qty:int}> $items */
                $items = collect($this->input('items', []))
                    ->map(fn (array $item): array => [
                        'book_id' => (int) $item['book_id'],
                        'qty' => (int) $item['qty'],
                    ]);

                $requestedBookIds = $items->pluck('book_id');
                $requestedBooksCount = $items->sum('qty');
                $activeBorrowingIds = $member->activeBorrowings()->pluck('id');
                $activeBooksCount = $activeBorrowingIds->isEmpty()
                    ? 0
                    : BorrowingItem::query()->whereIn('borrowing_id', $activeBorrowingIds)->sum('qty');

                if ($activeBooksCount + $requestedBooksCount > 3) {
                    $validator->errors()->add('items', 'Anggota hanya boleh memiliki maksimal 3 buku aktif dalam waktu bersamaan.');
                }

                if ($activeBorrowingIds->isNotEmpty()) {
                    $duplicatedBookIds = BorrowingItem::query()
                        ->whereIn('borrowing_id', $activeBorrowingIds)
                        ->whereIn('book_id', $requestedBookIds)
                        ->pluck('book_id');

                    if ($duplicatedBookIds->isNotEmpty()) {
                        $duplicatedTitles = Book::query()
                            ->whereIn('id', $duplicatedBookIds)
                            ->pluck('title')
                            ->implode(', ');

                        $validator->errors()->add('items', "Anggota masih memiliki pinjaman aktif untuk judul berikut: {$duplicatedTitles}.");
                    }
                }

                $selectedBooks = Book::query()
                    ->select(['id', 'title', 'stock'])
                    ->whereIn('id', $requestedBookIds)
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    /** @var Book|null $book */
                    $book = $selectedBooks->get($item['book_id']);

                    if (! $book || $book->stock < $item['qty']) {
                        $bookTitle = $book?->title ?? 'Buku terpilih';

                        $validator->errors()->add('items', "{$bookTitle} tidak memiliki stok cukup untuk dipinjam.");
                    }
                }
            },
        ];
    }
}
