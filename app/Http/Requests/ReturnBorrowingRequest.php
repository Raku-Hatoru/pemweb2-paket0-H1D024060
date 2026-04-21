<?php

namespace App\Http\Requests;

use App\Models\Borrowing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReturnBorrowingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var Borrowing $borrowing */
        $borrowing = $this->route('borrowing');

        return [
            'return_date' => ['required', 'date', 'after_or_equal:'.$borrowing->borrow_date->toDateString()],
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
                /** @var Borrowing $borrowing */
                $borrowing = $this->route('borrowing');

                if ($borrowing->isReturned()) {
                    $validator->errors()->add('return_date', 'Transaksi ini sudah dikembalikan sebelumnya.');
                }
            },
        ];
    }
}
