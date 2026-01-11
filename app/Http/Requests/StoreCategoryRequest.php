<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('user_id', Auth::user()?->id);
                }),
            ],
            'priority' => 'required|integer|min:1|max:10',
            'description' => 'required|string|max:1000',
            'archive_after_processing' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'You already have a category with this name.',
            'priority.required' => 'Importance level is required.',
            'priority.min' => 'Importance level must be at least 1.',
            'priority.max' => 'Importance level must not exceed 10.',
            'description.required' => 'Description is required to help AI categorize emails.',
            'description.max' => 'Description must not exceed 1000 characters.',
        ];
    }
}
