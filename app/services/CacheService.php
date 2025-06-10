<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Categoria;
use App\Models\Alimento;
use Carbon\Carbon;

/**
 * Serviço responsável por gerenciar o cache do sistema
 * 
 * Este serviço fornece uma interface unificada para operações de cache,
 * permitindo armazenar e recuperar dados de forma otimizada.
 */
class CacheService
{
    /**
     * Driver de cache padrão
     */
    protected $store;

    /**
     * Construtor - Configura o driver de cache
     */
    public function __construct()
    {
        $this->store = Cache::store(config('cache.default'));
    }

    /**
     * Armazena um valor no cache
     * 
     * @param string $key Chave para identificar o valor
     * @param mixed $value Valor a ser armazenado
     * @param int $minutes Tempo de expiração em minutos
     * @return bool
     */
    public function put(string $key, $value, int $minutes = 60): bool
    {
        try {
            return $this->store->put($key, $value, now()->addMinutes($minutes));
        } catch (\Exception $e) {
            \Log::error("Erro ao armazenar no cache: {$e->getMessage()}", [
                'key' => $key,
                'minutes' => $minutes
            ]);
            return false;
        }
    }

    /**
     * Recupera um valor do cache
     * 
     * @param string $key Chave do valor
     * @param mixed $default Valor padrão se não encontrado
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try {
            return $this->store->get($key, $default);
        } catch (\Exception $e) {
            \Log::error("Erro ao recuperar do cache: {$e->getMessage()}", [
                'key' => $key
            ]);
            return $default;
        }
    }

    /**
     * Remove um valor do cache
     * 
     * @param string $key Chave do valor a ser removido
     * @return bool
     */
    public function forget(string $key): bool
    {
        try {
            return $this->store->forget($key);
        } catch (\Exception $e) {
            \Log::error("Erro ao remover do cache: {$e->getMessage()}", [
                'key' => $key
            ]);
            return false;
        }
    }

    /**
     * Verifica se uma chave existe no cache
     * 
     * @param string $key Chave a ser verificada
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            return $this->store->has($key);
        } catch (\Exception $e) {
            \Log::error("Erro ao verificar cache: {$e->getMessage()}", [
                'key' => $key
            ]);
            return false;
        }
    }

    /**
     * Limpa todo o cache
     * 
     * @return bool
     */
    public function flush(): bool
    {
        try {
            return $this->store->flush();
        } catch (\Exception $e) {
            \Log::error("Erro ao limpar cache: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Tempo padrão de cache (24 horas)
     */
    const DEFAULT_TTL = 86400;

    /**
     * Retorna todas as categorias com cache
     */
    public static function getCategorias()
    {
        return Cache::remember('categorias', self::DEFAULT_TTL, function () {
            return Categoria::orderBy('nome')->get();
        });
    }

    /**
     * Retorna estatísticas do usuário com cache
     */
    public static function getEstatisticasUsuario($userId)
    {
        $cacheKey = "estatisticas_usuario_{$userId}";
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $alimentos = Alimento::where('user_id', $userId)->get();
            
            return [
                'total_alimentos' => $alimentos->count(),
                'proximos_vencer' => $alimentos->where('validade', '<=', Carbon::now()->addDays(7))->count(),
                'por_categoria' => $alimentos->groupBy('categoria_id')
                    ->map(fn($items) => $items->count()),
                'tipos_quantidade' => $alimentos->groupBy('tipo_quantidade')
                    ->map(fn($items) => $items->count())
            ];
        });
    }

    /**
     * Limpa o cache relacionado a um usuário
     */
    public static function limparCacheUsuario($userId)
    {
        Cache::forget("estatisticas_usuario_{$userId}");
        Cache::tags(['alimentos', "user_{$userId}"])->flush();
    }

    /**
     * Limpa o cache de categorias
     */
    public static function limparCacheCategorias()
    {
        Cache::forget('categorias');
    }

    /**
     * Cache de alimentos por categoria
     */
    public static function getAlimentosPorCategoria($userId, $categoriaId)
    {
        $cacheKey = "alimentos_categoria_{$categoriaId}_user_{$userId}";
        return Cache::remember($cacheKey, 3600, function () use ($userId, $categoriaId) {
            return Alimento::with('categoria')
                ->where('user_id', $userId)
                ->where('categoria_id', $categoriaId)
                ->get();
        });
    }
} 