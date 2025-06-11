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
 */
class Alimento extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'alimentos';

    protected $fillable = [
        'nome',
        'tipo_quantidade',
        'quantidade',
        'validade',
        'categoria_id',
        'user_id',
        'sugestao'
    ];

    protected $casts = [
        'validade' => 'date',
        'quantidade' => 'float',
    ];

    // Regras de validação para o alimento
    public static $rules = [
        'nome' => 'required|string|max:255|min:3',
        'tipo_quantidade' => 'required|in:unidade,quilo,litro',
        'quantidade' => 'required|numeric|min:0.1',
        'validade' => 'required|date|after_or_equal:today',
        'categoria_id' => 'required|exists:categorias,id',
        'sugestao' => 'nullable|string|max:255'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Escopo: Alimentos próximos do vencimento
    public function scopeProximosDoVencimento($query, $dias = 7)
    {
        return $query->whereBetween('validade', [
            now(),
            now()->addDays($dias)
        ]);
    }

    // Escopo: Alimentos vencidos
    public function scopeVencidos($query)
    {
        return $query->where('validade', '<', now());
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    // Formata a quantidade para exibição
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

    // Status do alimento (Normal, Próximo do vencimento, Vencido)
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

    // Dias restantes para vencer
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

    public function getDataValidadeAttribute()
    {
        return $this->validade ? $this->validade->format('d/m/Y') : '';
    }

    public function getVencidoAttribute()
    {
        return $this->validade ? $this->validade->isPast() : false;
    }

    // Próximo do vencimento (3 dias ou menos)
    public function getVencendoAttribute()
    {
        if (!$this->validade) {
            return false;
        }
        $diasParaVencer = now()->diffInDays($this->validade, false);
        return $diasParaVencer >= 0 && $diasParaVencer <= 3;
    }

    // Sugestão de receita para o alimento
    public function getSugestaoAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
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

        static::saving(function ($alimento) {
            if ($alimento->quantidade < 0) {
                $alimento->quantidade = 0;
            }
            if (strlen($alimento->nome) > 255) {
                $alimento->nome = substr($alimento->nome, 0, 255);
            }
        });
    }
}
