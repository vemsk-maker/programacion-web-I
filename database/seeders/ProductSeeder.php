<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [category_name, product_name, unit, use_batches, barcodes[]]
        $products = [
            // Lácteos
            ['Leche',       'Leche PIL Entera 1L',          'unidad',  true,  ['7790895000282']],
            ['Leche',       'Leche PIL Semidescremada 1L',  'unidad',  true,  ['7790895000299']],
            ['Yogurt',      'Yogurt PIL Frutado 1L',        'unidad',  true,  ['7790895001001']],
            ['Quesos',      'Queso Fresco PIL 500g',        'unidad',  true,  ['7790895002001']],
            ['Huevos',      'Huevos Blancos x12',           'unidad',  false, ['7791234560001']],
            // Bebidas
            ['Gaseosas',    'Coca-Cola 2L',                 'unidad',  false, ['7501055301898']],
            ['Gaseosas',    'Pepsi 2L',                     'unidad',  false, ['7501031010652']],
            ['Aguas y Jugos','Agua Vital 600ml',            'unidad',  false, ['7791000100001']],
            ['Aguas y Jugos','Jugo Watts Naranja 1L',       'unidad',  true,  ['7802900001001']],
            ['Cervezas y Licores','Cerveza Huari 620ml',    'unidad',  false, ['7791300010001']],
            // Abarrotes
            ['Aceites y Vinagres','Aceite Fino 1L',         'unidad',  true,  ['7790070100001']],
            ['Harinas y Azúcares','Azúcar Blanca 1kg',      'kg',      false, ['7791500020001']],
            ['Harinas y Azúcares','Harina de Trigo 1kg',    'kg',      true,  ['7791500030001']],
            ['Arroz y Fideos',    'Arroz Doña Isabel 1kg',  'kg',      true,  ['7791200010001']],
            ['Conservas y Enlatados','Atún Real 170g',      'unidad',  true,  ['7790300010001']],
            // Snacks
            ['Galletas',    'Galletas Oreo 432g',           'unidad',  true,  ['7622300441203']],
            ['Papas y Fritos','Papas Lays Clásicas 42g',   'unidad',  false, ['7501019000918']],
            ['Chocolates y Dulces','Chocolate Sublime 32g','unidad',  true,  ['7751519100001']],
            // Higiene
            ['Jabones y Shampoo','Shampoo Head & Shoulders 400ml','unidad',true,['7500435100001']],
            ['Detergentes', 'Detergente Omo 1kg',           'unidad',  false, ['7891150046656']],
        ];

        foreach ($products as [$categoryName, $productName, $unit, $useBatches, $barcodes]) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            $product = Product::firstOrCreate(
                ['name' => $productName],
                [
                    'category_id'     => $category->id,
                    'use_batches'     => $useBatches,
                    'unit_of_measure' => $unit,
                    'active'          => true,
                ]
            );

            foreach ($barcodes as $code) {
                ProductBarcode::firstOrCreate(
                    ['product_id' => $product->id, 'barcode' => $code],
                    ['units_per_scan' => 1]
                );
            }
        }
    }
}
