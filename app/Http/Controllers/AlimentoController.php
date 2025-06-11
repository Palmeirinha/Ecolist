<?php
namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;
use App\Services\ReceitaService;
use App\Models\Categoria;
use App\Http\Requests\AlimentoRequest;

class AlimentoController extends Controller
{
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

    public function create()
    {
        $categorias = Categoria::all();
        return view('alimentos.create', compact('categorias'));
    }

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
            $dados['user_id'] = auth()->id();
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

    // Busca receitas baseadas nos alimentos do usuário
    public function buscarReceitas(ReceitaService $receitaService)
    {
        $alimentos = Alimento::where('user_id', auth()->id())->pluck('nome')->toArray();
        $todasReceitas = [];

        foreach ($alimentos as $alimento) {
            $receitas = $receitaService->buscarReceitas($alimento);
            if (!empty($receitas)) {
                $todasReceitas = array_merge($todasReceitas, $receitas);
            }
        }

        $receitasUnicas = collect($todasReceitas)->unique('id')->values()->all();

        return view('alimentos.receitas', ['receitas' => $receitasUnicas]);
    }
}
