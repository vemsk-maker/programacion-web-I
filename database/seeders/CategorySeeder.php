<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'Lácteos y Huevos' => ['Leche', 'Yogurt', 'Quesos', 'Mantequilla', 'Huevos'],
            'Bebidas'          => ['Aguas y Jugos', 'Gaseosas', 'Cervezas y Licores', 'Energizantes'],
            'Abarrotes'        => ['Aceites y Vinagres', 'Harinas y Azúcares', 'Arroz y Fideos', 'Conservas y Enlatados'],
            'Carnes y Embutidos' => ['Carnes Frescas', 'Embutidos', 'Mariscos'],
            'Snacks y Confitería' => ['Galletas', 'Chocolates y Dulces', 'Papas y Fritos'],
            'Higiene y Limpieza' => ['Jabones y Shampoo', 'Detergentes', 'Desinfectantes'],
        ];

        foreach ($groups as $parentName => $children) {
            $parent = Category::firstOrCreate(['name' => $parentName, 'parent_id' => null]);

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['name' => $childName, 'parent_id' => $parent->id]
                );
            }
        }
    }
}
