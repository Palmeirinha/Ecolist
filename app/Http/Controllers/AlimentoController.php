<?php
namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;
use App\Services\ReceitaService;
use App\Models\Categoria;
use App\Http\Requests\AlimentoRequest;

/**
 * Controlador responsável por gerenciar os alimentos do usuário.
 * Aqui você pode cadastrar, editar, listar, excluir e buscar receitas com base nos alimentos cadastrados.
 */
class AlimentoController extends Controller
{
    /**
     * Mostra a lista de alimentos do usuário, com filtros opcionais.
     */
    public function index(Request $request)
    {
        // Pega o usuário que está logado no sistema
        $user = auth()->user();
        
        // Começa a montar a consulta dos alimentos, já trazendo a categoria de cada um
        $query = $user->alimentos()->with('categoria');
        
        // Se o usuário quiser filtrar por status (vencidos, vencendo ou normais)
        if ($request->has('status')) {
            switch ($request->status) {
                case 'vencidos':
                    $query->vencidos();
                    break;
                case 'vencendo':
                    $query->proximosDoVencimento();
                    break;
                case 'normais':
                    $query->where('validade', '>', now()->addDays(7));
                    break;
            }
        }

        // Permite filtrar por categoria, se o usuário escolher uma
        if ($request->has('categoria_id')) {
            $query->porCategoria($request->categoria_id);
        }

        // Pega os alimentos já filtrados e ordenados pela validade, mostrando 10 por página
        $alimentos = $query->orderBy('validade')->paginate(10);
        
        // Busca todas as categorias para mostrar no filtro da tela
        $categorias = Categoria::all();
        
        // Retorna a tela de listagem, enviando os alimentos e categorias para a view
        return view('alimentos.index', compact('alimentos', 'categorias'));
    }

    /**
     * Mostra o formulário para cadastrar um novo alimento.
     */
    public function create()
    {
        // Busca todas as categorias para o usuário escolher ao cadastrar
        $categorias = Categoria::all();
        return view('alimentos.create', compact('categorias'));
    }

    /**
     * Salva um novo alimento no banco de dados.
     */
    public function store(AlimentoRequest $request)
    {
        try {
            $dados = $request->validated();
            
            // Confere se a categoria escolhida existe mesmo
            $categoria = Categoria::find($dados['categoria_id']);
            if (!$categoria) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'A categoria selecionada não existe.');
            }
            
            // Associa o alimento ao usuário logado
            $dados['user_id'] = auth()->id();
            $alimento = Alimento::create($dados);

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento cadastrado com sucesso!');
        } catch (\Exception $e) {
            // Se der algum erro, volta para o formulário e mostra a mensagem
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar alimento. Por favor, tente novamente.');
        }
    }

    /**
     * Mostra o formulário para editar um alimento já cadastrado.
     */
    public function edit(Alimento $alimento)
    {
        // Só deixa editar se o alimento for do usuário logado
        if ($alimento->user_id !== auth()->id()) {
            return redirect()
                ->route('alimentos.index')
                ->with('error', 'Você não tem permissão para editar este alimento.');
        }

        $categorias = Categoria::all();
        return view('alimentos.edit', compact('alimento', 'categorias'));
    }

    /**
     * Atualiza as informações de um alimento já existente.
     */
    public function update(AlimentoRequest $request, Alimento $alimento)
    {
        try {
            // Garante que só o dono pode atualizar
            if ($alimento->user_id !== auth()->id()) {
                return redirect()
                    ->route('alimentos.index')
                    ->with('error', 'Você não tem permissão para editar este alimento.');
            }

            $dados = $request->validated();
            $alimento->update($dados);

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar alimento. Por favor, tente novamente.');
        }
    }

    /**
     * Exclui um alimento do sistema.
     */
    public function destroy(Alimento $alimento)
    {
        try {
            // Só o dono pode excluir o alimento
            if ($alimento->user_id !== auth()->id()) {
                return redirect()
                    ->route('alimentos.index')
                    ->with('error', 'Você não tem permissão para excluir este alimento.');
            }

            $alimento->delete();

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir alimento. Por favor, tente novamente.');
        }
    }

    /**
     * Busca receitas que podem ser feitas com os alimentos cadastrados pelo usuário.
     */
    public function buscarReceitas(ReceitaService $receitaService)
    {
        // Pega o nome de todos os alimentos do usuário
        $alimentos = Alimento::where('user_id', auth()->id())->pluck('nome')->toArray();
        $todasReceitas = [];

        // Para cada alimento, busca receitas relacionadas
        foreach ($alimentos as $alimento) {
            $receitas = $receitaService->buscarReceitas($alimento);
            if (!empty($receitas)) {
                $todasReceitas = array_merge($todasReceitas, $receitas);
            }
        }

        // Remove receitas repetidas
        $receitasUnicas = collect($todasReceitas)->unique('id')->values()->all();

        // Mostra a tela com as receitas encontradas
        return view('alimentos.receitas', ['receitas' => $receitasUnicas]);
    }
}
