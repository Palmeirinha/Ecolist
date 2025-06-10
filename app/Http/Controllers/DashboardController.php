<?php

namespace App\Http\Controllers;

use App\Models\Alimento;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
  
    /**
     * Exibe o dashboard com estatísticas dos alimentos
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtém o ID do usuário autenticado
        $userId = auth()->id();
        
        // Calcula o total de alimentos cadastrados pelo usuário
        $total = Alimento::where('user_id', $userId)->count();
        
        // Busca alimentos que vencem nos próximos 3 dias
        $vencendo = Alimento::where('user_id', $userId)
            ->whereDate('validade', '<=', now()->addDays(3))
            ->whereDate('validade', '>=', now())
            ->count();
            
        // Busca alimentos já vencidos
        $vencidos = Alimento::where('user_id', $userId)
            ->whereDate('validade', '<', now())
            ->count();

        // Obtém os 5 alimentos mais recentemente cadastrados
        $alimentosRecentes = Alimento::with('categoria')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Lista alimentos próximos do vencimento para alerta
        $alimentosVencendo = Alimento::with('categoria')
            ->where('user_id', $userId)
            ->whereDate('validade', '<=', now()->addDays(3))
            ->whereDate('validade', '>=', now())
            ->orderBy('validade')
            ->get();

        // Gera resumo estatístico por categoria
        $resumoCategorias = Categoria::select('categorias.nome', DB::raw('COUNT(alimentos.id) as total'))
            ->leftJoin('alimentos', function($join) use ($userId) {
                $join->on('categorias.id', '=', 'alimentos.categoria_id')
                    ->where('alimentos.user_id', '=', $userId)
                    ->whereNull('alimentos.deleted_at');
            })
            ->groupBy('categorias.id', 'categorias.nome')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function($categoria) {
                return [
                    'nome' => $categoria->nome,
                    'total' => $categoria->total,
                    'porcentagem' => $categoria->total > 0 ? number_format(($categoria->total / Alimento::count()) * 100, 1) : 0
                ];
            });

        // Calcula estatísticas dos últimos 7 dias
        $estatisticasSemana = [
            'cadastrados' => Alimento::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'vencidos' => Alimento::where('user_id', $userId)
                ->whereDate('validade', '>=', now()->subDays(7))
                ->whereDate('validade', '<', now())
                ->count()
        ];

        // Retorna a view com todos os dados calculados
        return view('dashboard', compact(
            'total',
            'vencendo',
            'vencidos',
            'alimentosRecentes',
            'alimentosVencendo',
            'resumoCategorias',
            'estatisticasSemana'
        ));
    }
}
