<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\StockCache;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id'        => ['required', 'integer', 'exists:locations,id'],
            'client_name'        => ['nullable', 'string', 'max:255'],
            'client_nit'         => ['nullable', 'string', 'max:50'],
            'lines'              => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines      = $this->input('lines', []);
            $locationId = $this->input('location_id');

            if (! $locationId || empty($lines)) {
                return;
            }

            // Verify user has access to the selected location
            $user = $this->user();
            if (! $user->hasLocationAccess((int) $locationId)) {
                $validator->errors()->add('location_id', 'No tiene acceso a esta ubicación.');
                return;
            }

            $productIds = collect($lines)
                ->pluck('product_id')
                ->unique()
                ->filter()
                ->values();

            // Sum all stock (across batches) per product at this location
            $stocks = StockCache::where('location_id', $locationId)
                ->whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as total')
                ->groupBy('product_id')
                ->pluck('total', 'product_id');

            $productNames = Product::whereIn('id', $productIds)->pluck('name', 'id');

            foreach ($lines as $i => $line) {
                $pid = $line['product_id'] ?? null;
                if (! $pid) {
                    continue;
                }

                $available = (float) ($stocks[$pid] ?? 0);
                $requested = (float) ($line['quantity'] ?? 0);

                if ($requested > $available) {
                    $pName = $productNames[$pid] ?? "Producto #{$pid}";
                    $validator->errors()->add(
                        "lines.{$i}.quantity",
                        "Stock insuficiente para \"{$pName}\": disponible {$available}, solicitado {$requested}."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'lines.required'              => 'Debe agregar al menos un producto al carrito.',
            'lines.min'                   => 'Debe agregar al menos un producto al carrito.',
            'lines.*.product_id.required' => 'Cada línea debe tener un producto.',
            'lines.*.quantity.required'   => 'Cada línea debe tener una cantidad.',
            'lines.*.quantity.min'        => 'La cantidad debe ser mayor a cero.',
            'lines.*.unit_price.required' => 'Cada línea debe tener un precio.',
            'lines.*.unit_price.min'      => 'El precio no puede ser negativo.',
        ];
    }
}
