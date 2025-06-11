<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReceitaService;

class ReceitaController extends Controller
{
    protected $receitaService;

    // Injeta o serviço de receitas ao criar o controller
    public function __construct(ReceitaService $receitaService)
    {
        $this->receitaService = $receitaService;
    }

    // Exibe a tela inicial de busca de receitas (sem resultados)
    public function index()
    {
        return view('receitas.buscar', ['receitas' => [], 'termo' => '']);
    }

    /**
     * Busca receitas baseadas em informações fornecidas pelo usuário
     */
    public function buscar(Request $request)
    {
        $termo = $request->input('termo'); // Pega o termo digitado pelo usuário
        
        if (empty($termo)) {
            // Se não digitou nada, redireciona com mensagem
            return redirect()
                ->route('receitas.index')
                ->with('info', 'Digite um termo para buscar receitas.');
        }

        try {
            // Tenta buscar receitas usando o serviço
            $receitas = $this->receitaService->buscarReceitas($termo);
            
            if (empty($receitas)) {
                // Se não encontrou receitas, mostra mensagem amigável
                return view('receitas.buscar', [
                    'receitas' => [],
                    'termo' => $termo,
                    'mensagem' => 'Nenhuma receita encontrada para o termo buscado.'
                ]);
            }
            
            // Se encontrou receitas, exibe normalmente
            return view('receitas.buscar', [
                'receitas' => $receitas,
                'termo' => $termo
            ]);
        } catch (\Exception $e) {
            // Em caso de erro, registra no log e mostra mensagem de erro para o usuário
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
