<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlimentoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceitaController;

// Rota principal que exibe a página de boas-vindas
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rota do dashboard, acessível apenas para usuários autenticados
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Grupo de rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    // Rotas para editar, atualizar e excluir o perfil do usuário
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotas para gerenciar alimentos (CRUD)
    Route::resource('alimentos', AlimentoController::class);

    // Rota para exibir as receitas do usuário
    Route::get('/minhas-receitas', [AlimentoController::class, 'buscarReceitas'])->name('alimentos.receitas');
    
    // Rotas relacionadas às receitas
    Route::get('/receitas', [ReceitaController::class, 'index'])->name('receitas.index');
    Route::get('/receitas/buscar', [ReceitaController::class, 'buscar'])->name('receitas.buscar');
    Route::get('/receitas/sugestoes', [ReceitaController::class, 'sugerirReceitas'])->name('receitas.sugestoes');
});

// Inclui as rotas de autenticação padrão do Laravel
require __DIR__.'/auth.php';
