<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Categoria - Representa uma categoria de alimentos no sistema
 */
class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'descricao'
    ];

    public static $rules = [
        'nome' => 'required|string|max:255|min:3|unique:categorias',
        'descricao' => 'nullable|string|max:1000'
    ];

    /**
     * Relacionamento: Categoria possui muitos Alimentos
     */
    public function alimentos()
    {
        return $this->hasMany(Alimento::class);
    }
}
