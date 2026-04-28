<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'             => ['required', 'integer', 'exists:suppliers,id'],
            'location_id'             => ['required', 'integer', 'exists:locations,id'],
            'reference_doc'           => ['nullable', 'string', 'max:100'],
            'notes'                   => ['nullable', 'string'],
            'lines'                   => ['required', 'array', 'min:1'],
            'lines.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity'        => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_cost'       => ['required', 'numeric', 'gte:0'],
            'lines.*.batch_code'      => ['nullable', 'string', 'max:100'],
            'lines.*.expiration_date' => ['nullable', 'date'],
        ];
    }

    /**
     * After base rules pass, enforce batch_code + expiration_date
     * for any product that has use_batches = true.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            // Resolve all product IDs in one query
            $productIds = collect($lines)
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values();

            $products = \App\Models\Product::whereIn('id', $productIds)
                ->get(['id', 'use_batches'])
                ->keyBy('id');

            foreach ($lines as $i => $line) {
                $productId = $line['product_id'] ?? null;
                $product   = $productId ? $products->get($productId) : null;

                if ($product && $product->use_batches) {
                    if (empty($line['batch_code'])) {
                        $validator->errors()->add(
                            "lines.{$i}.batch_code",
                            'El código de lote es requerido para este producto.'
                        );
                    }
                    if (empty($line['expiration_date'])) {
                        $validator->errors()->add(
                            "lines.{$i}.expiration_date",
                            'La fecha de vencimiento es requerida para este producto.'
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'        => 'Seleccione un proveedor.',
            'supplier_id.exists'          => 'El proveedor seleccionado no existe.',
            'location_id.required'        => 'Seleccione la ubicación de destino.',
            'location_id.exists'          => 'La ubicación seleccionada no existe.',
            'lines.required'              => 'Debe agregar al menos un producto.',
            'lines.min'                   => 'Debe agregar al menos un producto.',
            'lines.*.product_id.required' => 'Seleccione un producto en cada línea.',
            'lines.*.product_id.exists'   => 'Uno de los productos no existe.',
            'lines.*.quantity.required'   => 'La cantidad es requerida.',
            'lines.*.quantity.gt'         => 'La cantidad debe ser mayor a 0.',
            'lines.*.unit_cost.required'  => 'El costo unitario es requerido.',
            'lines.*.unit_cost.gte'       => 'El costo no puede ser negativo.',
        ];
    }
}
