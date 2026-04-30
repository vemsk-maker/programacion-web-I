<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockCache;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $service      = app(InventoryService::class);
        $master       = User::where('email', 'master@psf.local')->firstOrFail();
        $supermercado = Location::where('name', 'Supermercado')->firstOrFail();
        $almacen      = Location::where('name', 'Almacén Central')->firstOrFail();

        $products = Product::all()->keyBy('name');

        // ── Traslado previo: mover stock del almacén al supermercado ──────────
        // Primero movemos un poco de stock al supermercado para poder vender
        $this->transferToStore($service, $master->id, $almacen->id, $supermercado->id, $products);

        // ── Venta 1 en el Supermercado ────────────────────────────────────────
        $service->registerSale([
            'user_id'       => $master->id,
            'location_id'   => $supermercado->id,
            'reference_doc' => 'VTA-001',
            'notes'         => null,
            'lines'         => [
                ['product_id' => $products['Coca-Cola 2L']->id,         'batch_id' => null, 'quantity' => 5,  'unit_cost' => 7.00],
                ['product_id' => $products['Leche PIL Entera 1L']->id,  'batch_id' => null, 'quantity' => 3,  'unit_cost' => 8.50],
                ['product_id' => $products['Galletas Oreo 432g']->id,   'batch_id' => null, 'quantity' => 2,  'unit_cost' => 18.00],
                ['product_id' => $products['Agua Vital 600ml']->id,     'batch_id' => null, 'quantity' => 6,  'unit_cost' => 2.50],
            ],
        ]);

        // ── Venta 2 en el Supermercado ────────────────────────────────────────
        $service->registerSale([
            'user_id'       => $master->id,
            'location_id'   => $supermercado->id,
            'reference_doc' => 'VTA-002',
            'notes'         => null,
            'lines'         => [
                ['product_id' => $products['Arroz Doña Isabel 1kg']->id, 'batch_id' => null, 'quantity' => 4, 'unit_cost' => 8.00],
                ['product_id' => $products['Azúcar Blanca 1kg']->id,    'batch_id' => null, 'quantity' => 2, 'unit_cost' => 6.00],
                ['product_id' => $products['Pepsi 2L']->id,             'batch_id' => null, 'quantity' => 3, 'unit_cost' => 6.50],
                ['product_id' => $products['Detergente Omo 1kg']->id,   'batch_id' => null, 'quantity' => 1, 'unit_cost' => 25.00],
            ],
        ]);

        // ── Venta 3 en el Supermercado ────────────────────────────────────────
        $service->registerSale([
            'user_id'       => $master->id,
            'location_id'   => $supermercado->id,
            'reference_doc' => 'VTA-003',
            'notes'         => null,
            'lines'         => [
                ['product_id' => $products['Yogurt PIL Frutado 1L']->id,           'batch_id' => null, 'quantity' => 2, 'unit_cost' => 12.00],
                ['product_id' => $products['Papas Lays Clásicas 42g']->id,         'batch_id' => null, 'quantity' => 5, 'unit_cost' => 5.50],
                ['product_id' => $products['Huevos Blancos x12']->id,              'batch_id' => null, 'quantity' => 3, 'unit_cost' => 14.00],
                ['product_id' => $products['Jugo Watts Naranja 1L']->id,           'batch_id' => null, 'quantity' => 2, 'unit_cost' => 11.00],
            ],
        ]);
    }

    private function transferToStore(
        InventoryService $service,
        int $userId,
        int $fromId,
        int $toId,
        $products
    ): void {
        // Solo transferir productos que tienen stock en almacén
        $stockInAlmacen = StockCache::where('location_id', $fromId)
            ->where('quantity', '>', 0)
            ->with('product')
            ->get()
            ->keyBy('product_id');

        $toTransfer = [
            'Coca-Cola 2L'          => 40,
            'Pepsi 2L'              => 30,
            'Agua Vital 600ml'      => 60,
            'Cerveza Huari 620ml'   => 24,
            'Leche PIL Entera 1L'   => 24,
            'Yogurt PIL Frutado 1L' => 12,
            'Galletas Oreo 432g'    => 12,
            'Papas Lays Clásicas 42g' => 30,
            'Arroz Doña Isabel 1kg' => 30,
            'Azúcar Blanca 1kg'     => 20,
            'Detergente Omo 1kg'    => 12,
        ];

        $lines = [];
        foreach ($toTransfer as $productName => $qty) {
            if (! isset($products[$productName])) {
                continue;
            }

            $productId = $products[$productName]->id;
            $cache     = $stockInAlmacen->get($productId);

            if (! $cache || $cache->quantity < $qty) {
                continue;
            }

            $lines[] = [
                'product_id' => $productId,
                'batch_id'   => $cache->batch_id,
                'quantity'   => $qty,
                'unit_cost'  => null,
            ];
        }

        if (! empty($lines)) {
            $service->registerTransfer([
                'user_id'            => $userId,
                'origin_location_id' => $fromId,
                'dest_location_id'   => $toId,
                'reference_doc'      => 'TRA-SEED-001',
                'notes'              => 'Traslado inicial de stock para pruebas',
                'lines'              => $lines,
            ]);
        }
    }
}
