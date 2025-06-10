<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Define uma rota protegida por autenticação usando o Sanctum.
// Quando um usuário autenticado faz uma requisição GET para /user,
// retorna as informações do usuário autenticado.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});