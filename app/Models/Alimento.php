<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Categoria;
use Carbon\Carbon;

/**
 * Modelo Alimento - Representa um alimento no sistema
 * 
 * Este modelo gerencia todas as operações relacionadas aos alimentos cadastrados pelos usuários,
 * incluindo validações, relacionamentos e regras de negócio específicas.
 * 
 * @property int $id Identificador único do alimento
 * @property string $nome Nome do alimento
 * @property string $tipo_quantidade Tipo de medida (unidade, quilo, litro)
 * @property float $quantidade Quantidade do alimento
 * @property date $validade Data de validade
 * @property int $categoria_id ID da categoria relacionada
 * @property int $user_id ID do usuário proprietário
 * @property \Carbon\Carbon $created_at Data de criação
 * @property \Carbon\Carbon $updated_at Data de atualização
 */
class Alimento extends Model
{
    use HasFactory;
    use SoftDeletes; // Permite exclusão lógica (soft delete)

    /**
     * Nome da tabela associada ao modelo
     *
     * @var string
     */
    protected $table = 'alimentos';

    /**
     * Atributos que podem ser preenchidos em massa
     * Protege contra vulnerabilidades de mass assignment
     */
    protected $fillable = [
        'nome',
        'tipo_quantidade',
        'quantidade',
        'validade',
        'categoria_id',
        'user_id',
        'sugestao'
    ];

    /**
     * Atributos que devem ser convertidos para tipos específicos
     */
    protected $casts = [
        'validade' => 'date',
        'quantidade' => 'float',
    ];

    /**
     * Regras de validação para o alimento
     * Usadas no AlimentoRequest para validar os dados de entrada
     */
    public static $rules = [
        'nome' => 'required|string|max:255|min:3',
        'tipo_quantidade' => 'required|in:unidade,quilo,litro',
        'quantidade' => 'required|numeric|min:0.1',
        'validade' => 'required|date|after_or_equal:today',
        'categoria_id' => 'required|exists:categorias,id',
        'sugestao' => 'nullable|string|max:255'
    ];

    /**
     * Relacionamento: Pertence a uma Categoria
     * Define a relação entre Alimento e Categoria (N:1)
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relacionamento: Pertence a um Usuário
     * Define a relação entre Alimento e User (N:1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopo: Alimentos próximos do vencimento
     * Filtra alimentos que vencem em até X dias
     */
    public function scopeProximosDoVencimento($query, $dias = 7)
    {
        return $query->whereBetween('validade', [
            now(),
            now()->addDays($dias)
        ]);
    }

    /**
     * Escopo: Alimentos vencidos
     * Filtra alimentos já vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('validade', '<', now());
    }

    /**
     * Escopo: Alimentos por categoria
     * Filtra alimentos de uma categoria específica
     */
    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Acessor: Formata a quantidade para exibição
     * Retorna a quantidade formatada com o tipo de medida
     */
    public function getQuantidadeFormatadaAttribute()
    {
        $quantidade = number_format($this->quantidade, 2, ',', '.');
        
        switch ($this->tipo_quantidade) {
            case 'unidade':
                return "{$quantidade} un";
            case 'quilo':
                return "{$quantidade} kg";
            case 'litro':
                return "{$quantidade} L";
            default:
                return $quantidade;
        }
    }

    /**
     * Acessor: Status do alimento
     * Retorna o status atual do alimento (Normal, Próximo do vencimento, Vencido)
     */
    public function getStatusAttribute()
    {
        if ($this->validade < now()) {
            return 'Vencido';
        }

        $diasParaVencer = now()->diffInDays($this->validade, false);
        
        if ($diasParaVencer <= 7) {
            return 'Próximo do vencimento';
        }

        return 'Normal';
    }

    /**
     * Calcula quantos dias faltam para o alimento vencer
     *
     * @return string
     */
    public function getDiasRestantesAttribute()
    {
        if (!$this->validade) {
            return '0 dias';
        }

        $hoje = Carbon::now()->startOfDay();
        $validade = Carbon::parse($this->validade)->startOfDay();
        $dias = $hoje->diffInDays($validade, false);

        if ($dias == 0) {
            return 'Vencendo Hoje';
        } elseif ($dias < 0) {
            return abs($dias) . ' dias (Vencido)';
        } else {
            return $dias . ' dias';
        }
    }

    /**
     * Acessor: Formata a data de validade
     */
    public function getDataValidadeAttribute()
    {
        return $this->validade ? $this->validade->format('d/m/Y') : '';
    }

    /**
     * Verifica se o alimento está vencido
     */
    public function getVencidoAttribute()
    {
        return $this->validade ? $this->validade->isPast() : false;
    }

    /**
     * Verifica se o alimento está próximo do vencimento (3 dias ou menos)
     */
    public function getVencendoAttribute()
    {
        if (!$this->validade) {
            return false;
        }

        $diasParaVencer = now()->diffInDays($this->validade, false);
        return $diasParaVencer >= 0 && $diasParaVencer <= 3;
    }

    /**
     * Acessor: Retorna a sugestão de receita para o alimento
     * Se não houver sugestão definida, retorna uma sugestão padrão baseada no nome
     */
    public function getSugestaoAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        // Sugestões padrão baseadas no nome do alimento
        $sugestoes = [
            'arroz' => 'Arroz à grega',
            'feijão' => 'Feijoada',
            'frango' => 'Frango assado',
            'carne' => 'Carne assada',
            'batata' => 'Purê de batata',
            'cenoura' => 'Bolo de cenoura',
            'leite' => 'Pudim de leite',
            'banana' => 'Bolo de banana',
            'maçã' => 'Torta de maçã',
            'chocolate' => 'Bolo de chocolate'
        ];

        $nomeNormalizado = mb_strtolower($this->nome);
        foreach ($sugestoes as $ingrediente => $sugestao) {
            if (str_contains($nomeNormalizado, $ingrediente)) {
                return $sugestao;
            }
        }

        return null;
    }

    // Boot: define eventos do modelo
    protected static function boot()
    {
        parent::boot();

        // Antes de salvar
        static::saving(function ($alimento) {
            // Garante que a quantidade seja positiva
            if ($alimento->quantidade < 0) {
                $alimento->quantidade = 0;
            }

            // Limita o tamanho do nome
            if (strlen($alimento->nome) > 255) {
                $alimento->nome = substr($alimento->nome, 0, 255);
            }
        });
    }
}
