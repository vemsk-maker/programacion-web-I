<?php

namespace App\Http\Requests;

use App\Models\StockCache;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_location_id'     => ['required', 'integer', 'exists:locations,id'],
            'to_location_id'       => ['required', 'integer', 'exists:locations,id', 'different:from_location_id'],
            'notes'                => ['nullable', 'string'],
            'lines'                => ['required', 'array', 'min:1'],
            'lines.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'lines.*.batch_id'     => ['nullable', 'integer', 'exists:batches,id'],
            'lines.*.quantity'     => ['required', 'numeric', 'gt:0'],
        ];
    }

    /**
     * After base rules, verify each line has enough stock at the origin.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fromLocationId = (int) $this->input('from_location_id');
            $lines          = $this->input('lines', []);

            foreach ($lines as $i => $line) {
                $productId = (int) ($line['product_id'] ?? 0);
                $batchId   = isset($line['batch_id']) && $line['batch_id'] !== '' ? (int) $line['batch_id'] : null;
                $quantity  = (float) ($line['quantity'] ?? 0);

                if (! $productId || ! $quantity) {
                    continue;
                }

                $available = StockCache::where('location_id', $fromLocationId)
                    ->where('product_id', $productId)
                    ->when($batchId !== null,
                        fn ($q) => $q->where('batch_id', $batchId),
                        fn ($q) => $q->whereNull('batch_id')
                    )
                    ->value('quantity') ?? 0;

                if ($quantity > (float) $available) {
                    $validator->errors()->add(
                        "lines.{$i}.quantity",
                        "Stock insuficiente. Disponible: {$available}."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'from_location_id.required'      => 'Seleccione la ubicación origen.',
            'from_location_id.exists'         => 'La ubicación origen no existe.',
            'to_location_id.required'         => 'Seleccione la ubicación destino.',
            'to_location_id.exists'           => 'La ubicación destino no existe.',
            'to_location_id.different'        => 'El origen y el destino deben ser distintos.',
            'lines.required'                  => 'Debe agregar al menos un producto.',
            'lines.min'                        => 'Debe agregar al menos un producto.',
            'lines.*.product_id.required'     => 'Seleccione un producto en cada línea.',
            'lines.*.product_id.exists'       => 'Uno de los productos no existe.',
            'lines.*.batch_id.exists'         => 'Uno de los lotes seleccionados no existe.',
            'lines.*.quantity.required'       => 'La cantidad es requerida.',
            'lines.*.quantity.gt'             => 'La cantidad debe ser mayor a 0.',
        ];
    }
}
