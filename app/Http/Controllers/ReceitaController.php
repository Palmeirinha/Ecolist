<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReceitaService;

class ReceitaController extends Controller
{
    protected $receitaService;

   
    public function __construct(ReceitaService $receitaService)
    {
        $this->receitaService = $receitaService;
    }

    public function index()
    {
        return view('receitas.buscar', ['receitas' => [], 'termo' => '']);
    }

    /**
     * Busca receitas baseadas em informacoes do usuario
     */
    public function buscar(Request $request)
    {
        $termo = $request->input('termo');
        
        if (empty($termo)) {
            return redirect()
                ->route('receitas.index')
                ->with('info', 'Digite um termo para buscar receitas.');
        }

        try {
            $receitas = $this->receitaService->buscarReceitas($termo);
            
            if (empty($receitas)) {
                return view('receitas.buscar', [
                    'receitas' => [],
                    'termo' => $termo,
                    'mensagem' => 'Nenhuma receita encontrada para o termo buscado.'
                ]);
            }
            
            return view('receitas.buscar', [
                'receitas' => $receitas,
                'termo' => $termo
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar receitas', [
                'termo' => $termo,
                'erro' => $e->getMessage()
            ]);
            
            return view('receitas.buscar', [
                'receitas' => [],
                'termo' => $termo,
                'erro' => 'Ocorreu um erro ao buscar as receitas. Por favor, tente novamente.'
            ]);
        }
    }
}
