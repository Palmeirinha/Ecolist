<?php
namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;
use App\Services\ReceitaService;
use App\Models\Categoria;
use App\Http\Requests\AlimentoRequest;

class AlimentoController extends Controller
{
    // Alimentos do usuário filtra por categoria 
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->alimentos()->with('categoria');
        $categoriaId = $request->categoria_id;
        if (!empty($categoriaId)) {
            $query->porCategoria($categoriaId); 
        }
        $alimentos = $query->orderBy('validade')->paginate(10); 
        $categorias = Categoria::all(); 
        return view('alimentos.index', compact('alimentos', 'categorias', 'categoriaId'));
    }

    // Mostra o formulário para cadastrar um novo alimento
    public function create()
    {
        $categorias = Categoria::all(); 
        return view('alimentos.create', compact('categorias'));
    }

    // Salva um novo alimento no banco de dados
    public function store(AlimentoRequest $request)
    {
        try {
            $dados = $request->validated(); 
            $categoria = Categoria::find($dados['categoria_id']);
            if (!$categoria) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'A categoria selecionada não existe.');
            }
            $dados['user_id'] = auth()->id(); // Associa o alimento ao usuário logado
            $alimento = Alimento::create($dados);

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar alimento. Por favor, tente novamente.');
        }
    }

    // Mostra o formulário para editar um alimento existente
    public function edit(Alimento $alimento)
    {
        if ($alimento->user_id !== auth()->id()) {
      
            return redirect()
                ->route('alimentos.index')
                ->with('error', 'Você não tem permissão para editar este alimento.');
        }

        $categorias = Categoria::all();
        return view('alimentos.edit', compact('alimento', 'categorias'));
    }

  
    public function update(AlimentoRequest $request, Alimento $alimento)
    {
        try {
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

    // Exclui um alimento do banco de dados
    public function destroy(Alimento $alimento)
    {
        try {
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

    // Busca receitas baseadas nos alimentos cadastrados pelo usuário
    public function buscarReceitas(ReceitaService $receitaService)
    {
        $alimentos = Alimento::where('user_id', auth()->id())->pluck('nome')->toArray(); // Pega nomes dos alimentos do usuário
        $todasReceitas = [];

        foreach ($alimentos as $alimento) {
            $receitas = $receitaService->buscarReceitas($alimento); // Busca receitas para cada alimento
            if (!empty($receitas)) {
                $todasReceitas = array_merge($todasReceitas, $receitas); // une receitas encontradas
            }
        }

        $receitasUnicas = collect($todasReceitas)->unique('id')->values()->all(); // Remove receitas duplicadas

        return view('alimentos.receitas', ['receitas' => $receitasUnicas]);
    }
}
