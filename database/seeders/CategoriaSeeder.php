<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Frutas',
            'Verduras',
            'Legumes',
            'Carnes',
            'Bebidas',
            'Laticínios',
            'Grãos',
            'Congelados'
        ];

        foreach ($categorias as $nome) {
            Categoria::firstOrCreate(['nome' => $nome]);
        }
    }
} 