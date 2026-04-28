<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'category_id'     => ['required', 'integer', 'exists:categories,id'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'use_batches'     => ['sometimes', 'boolean'],
            'active'          => ['sometimes', 'boolean'],
            'barcodes'              => ['nullable', 'array'],
            'barcodes.*.barcode'    => ['required_with:barcodes.*', 'string', 'max:100'],
            'barcodes.*.units_per_scan' => ['required_with:barcodes.*', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'use_batches' => $this->boolean('use_batches'),
            'active'      => $this->boolean('active'),
        ]);
    }
}
