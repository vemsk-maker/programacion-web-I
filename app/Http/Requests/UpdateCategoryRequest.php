<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category');

        return [
            'name'      => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', "not_in:{$id}"],
        ];
    }
}
