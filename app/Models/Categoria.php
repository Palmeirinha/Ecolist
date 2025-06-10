<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Categoria - Representa uma categoria de alimentos no sistema
 * 
 * Este modelo gerencia as categorias que podem ser atribuídas aos alimentos,
 * permitindo uma melhor organização e classificação dos itens.
 * 
 * @property int $id Identificador único da categoria
 * @property string $nome Nome da categoria
 * @property string $descricao Descrição da categoria
 * @property \Carbon\Carbon $created_at Data de criação
 * @property \Carbon\Carbon $updated_at Data de atualização
 */
class Categoria extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo
     *
     * @var string
     */
    protected $table = 'categorias';

    /**
     * Atributos que podem ser preenchidos em massa
     * Protege contra vulnerabilidades de mass assignment
     */
    protected $fillable = [
        'nome',
        'descricao'
    ];

    /**
     * Regras de validação para a categoria
     */
    public static $rules = [
        'nome' => 'required|string|max:255|min:3|unique:categorias',
        'descricao' => 'nullable|string|max:1000'
    ];

    /**
     * Define o relacionamento com os alimentos desta categoria
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alimentos()
    {
        return $this->hasMany(Alimento::class);
    }
}
