<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Distribuidora Pil Andina',    'nit' => '1020304050',  'contact_info' => 'ventas@pilandina.com.bo'],
            ['name' => 'Embol S.A.',                  'nit' => '2030405060',  'contact_info' => 'pedidos@embol.com.bo'],
            ['name' => 'Unilever Bolivia',            'nit' => '3040506070',  'contact_info' => '+591 2 2123456'],
            ['name' => 'Alicorp Bolivia',             'nit' => '4050607080',  'contact_info' => 'alicorp@ventas.bo'],
            ['name' => 'Casa Reyes (importador)',     'nit' => '5060708090',  'contact_info' => '+591 70012345'],
            ['name' => 'Mercado Central (proveedor)', 'nit' => null,          'contact_info' => null],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['name' => $s['name']], [
                'nit'          => $s['nit'],
                'contact_info' => $s['contact_info'],
                'active'       => true,
            ]);
        }
    }
}
