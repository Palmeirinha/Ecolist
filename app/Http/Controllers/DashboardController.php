<?php

namespace App\Http\Controllers;

use App\Models\Alimento;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard com estatísticas dos alimentos do usuário
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userId = auth()->id(); 

        // total alimentos cadastrados pelo usuário
        $total = Alimento::where('user_id', $userId)->count();

        //  alimentos que vão vencer 
        $vencendo = Alimento::where('user_id', $userId)
            ->whereDate('validade', '<=', now()->addDays(3))
            ->whereDate('validade', '>=', now())
            ->count();

        //  alimentos vencidos
        $vencidos = Alimento::where('user_id', $userId)
            ->whereDate('validade', '<', now())
            ->count();

        // alimentos recentemente cadastrados
        $alimentosRecentes = Alimento::with('categoria')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $alimentosVencendo = Alimento::with('categoria')
            ->where('user_id', $userId)
            ->whereDate('validade', '<=', now()->addDays(3))
            ->whereDate('validade', '>=', now())
            ->orderBy('validade')
            ->get();

        //  alimentos existentes por categoria
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

        // alimentos cadastrados e vencidos 
        $estatisticasSemana = [
            'cadastrados' => Alimento::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'vencidos' => Alimento::where('user_id', $userId)
                ->whereDate('validade', '>=', now()->subDays(7))
                ->whereDate('validade', '<', now())
                ->count()
        ];

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
