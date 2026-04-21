<?php

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreMemberRequest extends FormRequest
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
        $memberCode = Str::upper(Str::squish((string) $this->input('member_code')));

        $this->merge([
            'name' => Str::squish((string) $this->input('name')),
            'email' => Str::lower(Str::squish((string) $this->input('email'))),
            'member_code' => $memberCode !== '' ? $memberCode : Member::nextMemberCode(),
            'phone' => Str::squish((string) $this->input('phone')),
            'address' => Str::squish((string) $this->input('address')),
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'member_code' => ['required', 'string', 'max:20', Rule::unique(Member::class, 'member_code')],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }
}
