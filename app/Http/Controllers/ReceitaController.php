<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReceitaService;


class ReceitaController extends Controller
{
   
    protected $receitaService;

    /**
     * Construtor - Injeta as dependências necessárias
     * 
     * @param ReceitaService $receitaService Serviço de receitas
     */
    public function __construct(ReceitaService $receitaService)
    {
        $this->receitaService = $receitaService;
    }

    /**
     * Exibe a página de busca de receitas
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('receitas.buscar', ['receitas' => [], 'termo' => '']);
    }

    /**
     * Realiza a busca de receitas baseada nos termos fornecidos
     * 
     * @param Request $request 
     * @return \Illuminate\View\View
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
            // Busca receitas
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

    /**
     * Sugere receitas com base em um ingrediente específico
     * 
     * @param Request $request
     * @param ReceitaService $receitaService
     * @return \Illuminate\View\View
     */
    public function sugerirReceitas(Request $request, ReceitaService $receitaService)
    {
        // Obtém o ingrediente da requisição
        $ingrediente = $request->input('ingrediente');
        $receitas = [];

        // Se houver um ingrediente, busca receitas relacionadas
        if ($ingrediente) {
            $receitas = $receitaService->buscarReceitas($ingrediente);
        }

        return view('receitas.sugestoes', compact('receitas', 'ingrediente'));
    }
}
