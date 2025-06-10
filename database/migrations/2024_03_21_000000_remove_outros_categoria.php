<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Categoria;
use App\Models\Alimento;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Encontra a categoria "Outros"
        $outrosCategoria = Categoria::where('nome', 'Outros')->first();

        if ($outrosCategoria) {
            // Encontra a categoria "Congelados" para mover os alimentos
            $congeladosCategoria = Categoria::where('nome', 'Congelados')->first();

            if ($congeladosCategoria) {
                // Atualiza os alimentos da categoria "Outros" para "Congelados"
                Alimento::where('categoria_id', $outrosCategoria->id)
                    ->update(['categoria_id' => $congeladosCategoria->id]);
            }

            // Remove a categoria "Outros"
            $outrosCategoria->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recria a categoria "Outros"
        $outrosCategoria = Categoria::create(['nome' => 'Outros']);

        if ($outrosCategoria) {
            // Encontra a categoria "Congelados"
            $congeladosCategoria = Categoria::where('nome', 'Congelados')->first();

            if ($congeladosCategoria) {
                // Move de volta os alimentos que eram da categoria "Outros"
                Alimento::where('categoria_id', $congeladosCategoria->id)
                    ->update(['categoria_id' => $outrosCategoria->id]);
            }
        }
    }
}; 