<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Almacén Central', 'type' => LocationType::Warehouse],
            ['name' => 'Supermercado',    'type' => LocationType::Store],
            ['name' => 'Licorería',       'type' => LocationType::Store],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['name' => $location['name']],
                ['type' => $location['type'], 'active' => true]
            );
        }
    }
}
