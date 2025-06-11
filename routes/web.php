<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlimentoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceitaController;

// Página inicial
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('alimentos', AlimentoController::class);

    Route::get('/minhas-receitas', [AlimentoController::class, 'buscarReceitas'])->name('alimentos.receitas');

    Route::get('/receitas', [ReceitaController::class, 'index'])->name('receitas.index');
    Route::get('/receitas/buscar', [ReceitaController::class, 'buscar'])->name('receitas.buscar');

    // Dashboard do usuário
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Rotas de autenticação padrão do Laravel
require __DIR__ . '/auth.php';
