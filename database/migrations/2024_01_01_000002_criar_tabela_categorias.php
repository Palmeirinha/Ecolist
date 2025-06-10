<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        // Inserir categorias padrão
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
            DB::table('categorias')->insert([
                'nome' => $nome,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
}; 