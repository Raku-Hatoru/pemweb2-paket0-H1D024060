<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $name = Str::squish((string) $this->input('name'));
        $slugInput = (string) $this->input('slug');

        $this->merge([
            'name' => $name,
            'slug' => Str::slug($slugInput !== '' ? $slugInput : $name),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:80', Rule::unique(Category::class, 'name')->ignore($category)],
            'slug' => ['required', 'string', 'max:100', Rule::unique(Category::class, 'slug')->ignore($category)],
        ];
    }
}
