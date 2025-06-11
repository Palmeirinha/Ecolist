<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Serviço responsável por gerenciar a busca e manipulação de receitas
 */
class ReceitaService
{
    protected $baseUrl;
    protected $cacheTimeout = 3600;

    protected const PONTUACAO = [
        'nome_exato' => 10,
        'ingrediente_exato' => 8,
        'nome_parcial' => 5,
        'ingrediente_parcial' => 3
    ];

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->baseUrl = config('services.receitas-api.base_url');
        $this->cacheService = $cacheService;
    }

    /**
     * Busca receitas baseadas em um termo de pesquisa
     */
    public function buscarReceitas(string $termo): array
    {
        try {
            $termoNormalizado = $this->normalizarTexto($termo);
            $cacheKey = "receitas:{$termoNormalizado}";
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::get("{$this->baseUrl}/receitas/todas");
            
            if (!$response->successful()) {
                Log::error('Erro na API de receitas', [
                    'status' => $response->status(),
                    'erro' => $response->body()
                ]);
                return [];
            }
            
            $todasReceitas = $response->json() ?? [];
            
            if (empty($todasReceitas)) {
                Log::info('Nenhuma receita encontrada na API', ['termo' => $termo]);
                return [];
            }

            $receitas = collect($todasReceitas)
                ->filter(function ($receita) use ($termoNormalizado) {
                    if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                        return false;
                    }

                    $nomeReceita = $this->normalizarTexto($receita['receita']);
                    $ingredientes = $this->normalizarTexto($receita['ingredientes']);

                    return str_contains($nomeReceita, $termoNormalizado) || 
                           str_contains($ingredientes, $termoNormalizado);
                })
                ->map(function ($receita) use ($termoNormalizado) {
                    $nomeReceita = $this->normalizarTexto($receita['receita']);
                    $ingredientes = $this->normalizarTexto($receita['ingredientes']);
                    
                    $receita['relevancia'] = $this->calcularPontuacao($nomeReceita, $ingredientes, $termoNormalizado);
                    return $this->formatarReceita($receita);
                })
                ->filter()
                ->sortByDesc('relevancia')
                ->take(12)
                ->values()
                ->all();

            Cache::put($cacheKey, $receitas, now()->addSeconds($this->cacheTimeout));
            
            return $receitas;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar receitas', [
                'termo' => $termo,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function calcularPontuacao(string $nomeReceita, string $ingredientes, string $termo): int
    {
        $pontuacao = 0;
        $palavras = array_filter(explode(' ', $termo));

        if ($nomeReceita === $termo) {
            $pontuacao += self::PONTUACAO['nome_exato'];
        }
        
        foreach ($palavras as $palavra) {
            if (str_contains($nomeReceita, $palavra)) {
                $pontuacao += self::PONTUACAO['nome_parcial'];
            }
        }

        if (str_contains($ingredientes, $termo)) {
            $pontuacao += self::PONTUACAO['ingrediente_exato'];
        }
        
        foreach ($palavras as $palavra) {
            if (str_contains($ingredientes, $palavra)) {
                $pontuacao += self::PONTUACAO['ingrediente_parcial'];
            }
        }

        return $pontuacao;
    }

    /**
     * Normaliza o texto removendo acentos e convertendo para minúsculas
     */
    protected function normalizarTexto(string $texto): string
    {
        if (empty($texto)) {
            return '';
        }
        
        $texto = mb_strtolower(trim($texto));
        
        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ñ' => 'n',
            'ç' => 'c',
        ];
        
        return strtr($texto, $map);
    }

    protected function formatarReceita($receita): ?array
    {
        try {
            if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                return null;
            }

            $ingredientes = array_filter(
                array_map('trim', explode(',', $receita['ingredientes'])),
                fn($item) => !empty($item)
            );

            return [
                'id' => $receita['id'] ?? uniqid(),
                'strMeal' => $receita['receita'],
                'strMealThumb' => $receita['link_imagem'] ?? 'https://via.placeholder.com/350x250.png?text=Imagem+não+disponível',
                'strCategory' => $receita['tipo'] ?? 'Não categorizado',
                'strArea' => 'Brasileira',
                'ingredientes' => array_map(fn($ingrediente) => [
                    'nome' => $ingrediente,
                    'medida' => $this->extrairMedida($ingrediente)
                ], $ingredientes),
                'instrucoes' => $receita['modo_preparo'] ?? '',
                'tempoPreparo' => $this->estimarTempoPreparo($receita['modo_preparo'] ?? ''),
                'porcoes' => 4,
                'url' => $receita['link_imagem'] ?? '',
                'tempoPreparoTexto' => $this->formatarTempoPreparo($this->estimarTempoPreparo($receita['modo_preparo'] ?? '')),
                'relevancia' => $receita['relevancia'] ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao formatar receita', [
                'receita' => $receita,
                'erro' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function estimarTempoPreparo(string $modoPreparo): int
    {
        $passos = substr_count($modoPreparo, '.') + substr_count($modoPreparo, "\n");
        return max(15, min(120, $passos * 10));
    }

    protected function extrairMedida(string $ingrediente): string
    {
        if (empty($ingrediente)) {
            return "A gosto";
        }

        $padroes = [
            '/(\d+)\s*(g|kg|ml|l|xícara|xícaras|colher|colheres|unidade|unidades)/i',
            '/(\d+)/i'
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $ingrediente, $matches)) {
                return $matches[0];
            }
        }

        return "A gosto";
    }

    protected function formatarTempoPreparo(int $minutos): string
    {
        if ($minutos < 60) {
            return "{$minutos} minutos";
        }
        $horas = floor($minutos / 60);
        $minutosRestantes = $minutos % 60;
        return $minutosRestantes > 0 
            ? "{$horas}h {$minutosRestantes}min" 
            : "{$horas}h";
    }

    /**
     * Busca receitas em lote para múltiplos termos
     */
    public function buscarReceitasEmLote(array $termos): array
    {
        try {
            $cacheKey = 'receitas_busca_lote_' . md5(implode('_', $termos));
            
            return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($termos) {
                $resultado = [];
                
                foreach ($termos as $termo) {
                    $receitas = $this->buscarReceitas($termo);
                    if (!empty($receitas)) {
                        $resultado[$termo] = reset($receitas);
                    }
                }
                
                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Erro ao buscar receitas em lote', [
                'termos' => $termos,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }
}
