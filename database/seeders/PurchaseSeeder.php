<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $service   = app(InventoryService::class);
        $master    = User::where('email', 'master@psf.local')->firstOrFail();
        $almacen   = Location::where('name', 'Almacén Central')->firstOrFail();
        $supermercado = Location::where('name', 'Supermercado')->firstOrFail();

        $pil      = Supplier::where('name', 'like', '%PIL%')->first();
        $embol    = Supplier::where('name', 'like', '%Embol%')->first();
        $alicorp  = Supplier::where('name', 'like', '%Alicorp%')->first();
        $casaReyes = Supplier::where('name', 'like', '%Reyes%')->first();

        $products = Product::all()->keyBy('name');

        // ── Compra 1: Lácteos y Bebidas al almacén central ────────────────────
        $service->registerPurchase([
            'user_id'       => $master->id,
            'location_id'   => $almacen->id,
            'supplier_id'   => $pil?->id,
            'reference_doc' => 'FAC-PIL-001',
            'notes'         => 'Compra inicial lácteos',
            'lines'         => [
                [
                    'product_id'      => $products['Leche PIL Entera 1L']->id,
                    'batch_code'      => 'PIL-2026-A',
                    'expiration_date' => Carbon::now()->addMonths(3)->toDateString(),
                    'quantity'        => 120,
                    'unit_cost'       => 8.50,
                ],
                [
                    'product_id'      => $products['Leche PIL Semidescremada 1L']->id,
                    'batch_code'      => 'PIL-2026-B',
                    'expiration_date' => Carbon::now()->addMonths(3)->toDateString(),
                    'quantity'        => 60,
                    'unit_cost'       => 9.00,
                ],
                [
                    'product_id'      => $products['Yogurt PIL Frutado 1L']->id,
                    'batch_code'      => 'PIL-YOG-01',
                    'expiration_date' => Carbon::now()->addMonths(2)->toDateString(),
                    'quantity'        => 48,
                    'unit_cost'       => 12.00,
                ],
                [
                    'product_id'      => $products['Queso Fresco PIL 500g']->id,
                    'batch_code'      => 'PIL-QSO-01',
                    'expiration_date' => Carbon::now()->addDays(20)->toDateString(),
                    'quantity'        => 30,
                    'unit_cost'       => 22.00,
                ],
            ],
        ]);

        // ── Compra 2: Gaseosas y Agua al almacén central ──────────────────────
        $service->registerPurchase([
            'user_id'       => $master->id,
            'location_id'   => $almacen->id,
            'supplier_id'   => $embol?->id,
            'reference_doc' => 'FAC-EMBOL-001',
            'notes'         => 'Compra inicial bebidas',
            'lines'         => [
                [
                    'product_id'      => $products['Coca-Cola 2L']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 200,
                    'unit_cost'       => 7.00,
                ],
                [
                    'product_id'      => $products['Pepsi 2L']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 100,
                    'unit_cost'       => 6.50,
                ],
                [
                    'product_id'      => $products['Agua Vital 600ml']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 240,
                    'unit_cost'       => 2.50,
                ],
                [
                    'product_id'      => $products['Cerveza Huari 620ml']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 144,
                    'unit_cost'       => 9.00,
                ],
            ],
        ]);

        // ── Compra 3: Abarrotes al almacén central ────────────────────────────
        $service->registerPurchase([
            'user_id'       => $master->id,
            'location_id'   => $almacen->id,
            'supplier_id'   => $alicorp?->id,
            'reference_doc' => 'FAC-ALC-001',
            'notes'         => 'Compra inicial abarrotes',
            'lines'         => [
                [
                    'product_id'      => $products['Aceite Fino 1L']->id,
                    'batch_code'      => 'ALC-ACE-01',
                    'expiration_date' => Carbon::now()->addYear()->toDateString(),
                    'quantity'        => 60,
                    'unit_cost'       => 13.00,
                ],
                [
                    'product_id'      => $products['Azúcar Blanca 1kg']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 100,
                    'unit_cost'       => 6.00,
                ],
                [
                    'product_id'      => $products['Arroz Doña Isabel 1kg']->id,
                    'batch_code'      => 'ALC-ARR-01',
                    'expiration_date' => Carbon::now()->addYear()->toDateString(),
                    'quantity'        => 150,
                    'unit_cost'       => 8.00,
                ],
                [
                    'product_id'      => $products['Atún Real 170g']->id,
                    'batch_code'      => 'ALC-ATN-01',
                    'expiration_date' => Carbon::now()->addYears(2)->toDateString(),
                    'quantity'        => 96,
                    'unit_cost'       => 11.50,
                ],
            ],
        ]);

        // ── Compra 4: Snacks e Higiene al almacén central ─────────────────────
        $service->registerPurchase([
            'user_id'       => $master->id,
            'location_id'   => $almacen->id,
            'supplier_id'   => $casaReyes?->id,
            'reference_doc' => 'FAC-CR-001',
            'notes'         => 'Compra inicial snacks e higiene',
            'lines'         => [
                [
                    'product_id'      => $products['Galletas Oreo 432g']->id,
                    'batch_code'      => 'CR-ORE-01',
                    'expiration_date' => Carbon::now()->addMonths(8)->toDateString(),
                    'quantity'        => 48,
                    'unit_cost'       => 18.00,
                ],
                [
                    'product_id'      => $products['Papas Lays Clásicas 42g']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 100,
                    'unit_cost'       => 5.50,
                ],
                [
                    'product_id'      => $products['Shampoo Head & Shoulders 400ml']->id,
                    'batch_code'      => 'CR-SHP-01',
                    'expiration_date' => Carbon::now()->addYears(2)->toDateString(),
                    'quantity'        => 36,
                    'unit_cost'       => 32.00,
                ],
                [
                    'product_id'      => $products['Detergente Omo 1kg']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 48,
                    'unit_cost'       => 25.00,
                ],
            ],
        ]);

        // ── Compra 5: Stock directo al Supermercado ───────────────────────────
        $service->registerPurchase([
            'user_id'       => $master->id,
            'location_id'   => $supermercado->id,
            'supplier_id'   => $pil?->id,
            'reference_doc' => 'FAC-PIL-002',
            'notes'         => 'Stock inicial supermercado',
            'lines'         => [
                [
                    'product_id'      => $products['Huevos Blancos x12']->id,
                    'batch_code'      => null,
                    'expiration_date' => null,
                    'quantity'        => 50,
                    'unit_cost'       => 14.00,
                ],
                [
                    'product_id'      => $products['Jugo Watts Naranja 1L']->id,
                    'batch_code'      => 'PIL-JUG-01',
                    'expiration_date' => Carbon::now()->addMonths(4)->toDateString(),
                    'quantity'        => 40,
                    'unit_cost'       => 11.00,
                ],
            ],
        ]);
    }
}
