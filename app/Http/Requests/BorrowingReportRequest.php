<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class BorrowingReportRequest extends FormRequest
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
        $month = trim((string) $this->input('month'));
        $dateFrom = (string) $this->input('date_from');
        $dateTo = (string) $this->input('date_to');

        if ($month === '' && $dateFrom === '' && $dateTo === '') {
            $month = now()->format('Y-m');
        }

        $this->merge([
            'month' => $month !== '' ? $month : null,
            'date_from' => $dateFrom !== '' ? $dateFrom : null,
            'date_to' => $dateTo !== '' ? $dateTo : null,
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
            'month' => ['nullable', 'date_format:Y-m'],
            'date_from' => ['nullable', 'date', 'required_with:date_to'],
            'date_to' => ['nullable', 'date', 'required_with:date_from', 'after_or_equal:date_from'],
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
                if ($this->filled('month') && ($this->filled('date_from') || $this->filled('date_to'))) {
                    $validator->errors()->add('month', 'Gunakan filter bulan atau rentang tanggal, jangan keduanya sekaligus.');
                }
            },
        ];
    }

    public function rangeStart(): Carbon
    {
        if ($this->filled('date_from')) {
            return Carbon::parse((string) $this->validated('date_from'))->startOfDay();
        }

        if ($this->filled('month')) {
            return Carbon::createFromFormat('Y-m', (string) $this->validated('month'))->startOfMonth();
        }

        return now()->startOfMonth();
    }

    public function rangeEnd(): Carbon
    {
        if ($this->filled('date_to')) {
            return Carbon::parse((string) $this->validated('date_to'))->endOfDay();
        }

        if ($this->filled('month')) {
            return Carbon::createFromFormat('Y-m', (string) $this->validated('month'))->endOfMonth();
        }

        return now()->endOfMonth();
    }

    public function periodLabel(): string
    {
        if ($this->filled('date_from')) {
            return $this->rangeStart()->translatedFormat('d M Y').' - '.$this->rangeEnd()->translatedFormat('d M Y');
        }

        return $this->rangeStart()->translatedFormat('F Y');
    }
}
