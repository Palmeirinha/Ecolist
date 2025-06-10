<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Serviço responsável por gerenciar a busca e manipulação de receitas
 * 
 * Este serviço implementa a lógica de negócio para busca de receitas,
 * incluindo normalização de texto, pontuação de relevância e cache.
 */
class ReceitaService
{
    /**
     * URL base da API de receitas
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Tempo de duração do cache em segundos (1 hora)
     *
     * @var int
     */
    protected $cacheTimeout = 3600;

    /**
     * Serviço de cache para otimizar as requisições
     */
    protected $cacheService;

    /**
     * Tempo de cache para as receitas em minutos
     */
    protected const CACHE_MINUTES = 60;

    /**
     * Pontuação para diferentes tipos de correspondência
     */
    protected const PONTUACAO = [
        'nome_exato' => 10,
        'ingrediente_exato' => 8,
        'nome_parcial' => 5,
        'ingrediente_parcial' => 3
    ];

    /**
     * Inicializa o serviço configurando a URL base e injetando o serviço de cache
     */
    public function __construct(CacheService $cacheService)
    {
        $this->baseUrl = config('services.receitas-api.base_url');
        $this->cacheService = $cacheService;
    }

    /**
     * Busca receitas baseadas em um termo de pesquisa
     * 
     * @param string $termo Termo de busca
     * @return array Receitas encontradas, ordenadas por relevância
     */
    public function buscarReceitas(string $termo): array
    {
        try {
            $termoNormalizado = $this->normalizarTexto($termo);
            $cacheKey = "receitas:{$termoNormalizado}";
            
            // Tenta recuperar do cache
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Busca receitas da API
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

            // Filtra e pontua as receitas
            $receitas = collect($todasReceitas)
                ->filter(function ($receita) use ($termo) {
                    // Verifica campos obrigatórios
                    if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                        return false;
                    }

                    $nomeReceita = mb_strtolower($receita['receita']);
                    $ingredientes = mb_strtolower($receita['ingredientes']);
                    $termoBusca = mb_strtolower($termo);

                    // Busca no nome da receita ou nos ingredientes
                    return str_contains($nomeReceita, $termoBusca) || 
                           str_contains($ingredientes, $termoBusca);
                })
                ->map(function ($receita) {
                    return $this->formatarReceita($receita);
                })
                ->filter()
                ->take(12)
                ->values()
                ->all();

            // Armazena no cache
            Cache::put($cacheKey, $receitas, now()->addMinutes(60));
            
            return $receitas;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar receitas', [
                'termo' => $termo,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Calcula a pontuação de relevância para cada receita
     * 
     * @param array $receitas Lista de receitas
     * @param string $termo Termo de busca normalizado
     * @return array Receitas com pontuação
     */
    protected function calcularRelevancia(array $receitas, string $termo): array
    {
        return array_map(function ($receita) use ($termo) {
            $pontuacao = 0;
            $nomeNormalizado = $this->normalizarTexto($receita['receita']);
            
            // Correspondência exata no nome
            if ($nomeNormalizado === $termo) {
                $pontuacao += self::PONTUACAO['nome_exato'];
            }
            
            // Correspondência parcial no nome
            foreach (explode(' ', $termo) as $palavra) {
                if (str_contains($nomeNormalizado, $palavra)) {
                    $pontuacao += self::PONTUACAO['nome_parcial'];
                }
            }
            
            // Correspondência nos ingredientes
            foreach ($receita['ingredientes'] as $ingrediente) {
                $ingredienteNormalizado = $this->normalizarTexto($ingrediente);
                
                if ($ingredienteNormalizado === $termo) {
                    $pontuacao += self::PONTUACAO['ingrediente_exato'];
                }
                
                foreach (explode(' ', $termo) as $palavra) {
                    if (str_contains($ingredienteNormalizado, $palavra)) {
                        $pontuacao += self::PONTUACAO['ingrediente_parcial'];
                    }
                }
            }
            
            $receita['relevancia'] = $pontuacao;
            return $receita;
        }, $receitas);
    }

    /**
     * Ordena as receitas por pontuação e limita a quantidade
     * 
     * @param array $receitas Receitas com pontuação
     * @return array Receitas ordenadas
     */
    protected function ordenarPorRelevancia(array $receitas): array
    {
        usort($receitas, function ($a, $b) {
            return $b['relevancia'] <=> $a['relevancia'];
        });
        
        return array_slice($receitas, 0, 12);
    }

    /**
     * Normaliza o texto removendo acentos e convertendo para minúsculas
     * 
     * @param string $texto Texto a ser normalizado
     * @return string Texto normalizado
     */
    protected function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower($texto);
        $texto = preg_replace('/[áàãâä]/u', 'a', $texto);
        $texto = preg_replace('/[éèêë]/u', 'e', $texto);
        $texto = preg_replace('/[íìîï]/u', 'i', $texto);
        $texto = preg_replace('/[óòõôö]/u', 'o', $texto);
        $texto = preg_replace('/[úùûü]/u', 'u', $texto);
        $texto = preg_replace('/[ç]/u', 'c', $texto);
        return trim($texto);
    }

    /**
     * Busca receitas da API externa
     * 
     * @param string $termo Termo de busca
     * @return array Receitas encontradas
     * @throws \Exception se houver erro na API
     */
    protected function buscarDaAPI(string $termo): array
    {
        try {
            $cacheKey = 'receitas_api_' . md5($termo);
            
            // Tenta recuperar do cache primeiro
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            $response = Http::get("{$this->baseUrl}/receitas/todas");
            
            if (!$response->successful()) {
                throw new \Exception("API retornou status {$response->status()}");
            }
            
            $todasReceitas = $response->json() ?? [];
            
            if (empty($todasReceitas)) {
                Log::warning('Nenhuma receita encontrada na API', ['termo' => $termo]);
                return [];
            }

            // Filtra as receitas que contêm o termo buscado
            $receitas = collect($todasReceitas)->filter(function ($receita) use ($termo) {
                if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                    return false;
                }

                $nomeReceita = strtolower($receita['receita']);
                $ingredientes = strtolower($receita['ingredientes']);
                $termoBusca = strtolower($termo);

                // Normaliza os textos (remove acentos)
                $nomeReceita = $this->removerAcentos($nomeReceita);
                $ingredientes = $this->removerAcentos($ingredientes);
                $termoBusca = $this->removerAcentos($termoBusca);

                // Divide o termo em palavras
                $palavrasTermoBusca = array_filter(explode(' ', $termoBusca));

                // Calcula a relevância da receita
                $pontuacao = 0;

                // Pontuação para correspondência exata no nome da receita
                if (str_contains($nomeReceita, $termoBusca)) {
                    $pontuacao += 10;
                }

                // Pontuação para palavras individuais no nome da receita
                foreach ($palavrasTermoBusca as $palavra) {
                    if (str_contains($nomeReceita, $palavra)) {
                        $pontuacao += 5;
                    }
                }

                // Pontuação para correspondência nos ingredientes
                if (str_contains($ingredientes, $termoBusca)) {
                    $pontuacao += 8;
                }

                // Pontuação para palavras individuais nos ingredientes
                foreach ($palavrasTermoBusca as $palavra) {
                    if (str_contains($ingredientes, $palavra)) {
                        $pontuacao += 3;
                    }
                }

                // Armazena a pontuação na receita
                $receita['relevancia'] = $pontuacao;

                // Retorna true se houver alguma relevância
                return $pontuacao > 0;
            })
            ->sortByDesc('relevancia') // Ordena por relevância
            ->take(12)
            ->values();

            // Formata cada receita encontrada
            $receitasFormatadas = $receitas->map(function ($receita) {
                return $this->formatarReceita($receita);
            })->filter()->values()->all();

            // Armazena no cache por 1 hora
            Cache::put($cacheKey, $receitasFormatadas, now()->addHour());
            
            return $receitasFormatadas;
        } catch (\Exception $e) {
            Log::error('Erro ao processar resposta da API', [
                'termo' => $termo,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Formata os dados da receita para o padrão do sistema
     *
     * @param array $receita
     * @return array|null
     */
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
                'informacaoNutricional' => [
                    'calorias' => 0,
                    'proteinas' => 0,
                    'carboidratos' => 0,
                    'gorduras' => 0,
                ],
                'url' => $receita['link_imagem'] ?? '',
                'vegetariano' => $this->verificarVegetariano($receita['ingredientes'] ?? ''),
                'vegano' => $this->verificarVegano($receita['ingredientes'] ?? ''),
                'semGluten' => $this->verificarSemGluten($receita['ingredientes'] ?? ''),
                'tempoPreparoTexto' => $this->formatarTempoPreparo($this->estimarTempoPreparo($receita['modo_preparo'] ?? ''))
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao formatar receita', [
                'receita' => $receita,
                'erro' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Estima o tempo de preparo baseado na complexidade do modo de preparo
     *
     * @param string $modoPreparo
     * @return int
     */
    protected function estimarTempoPreparo(string $modoPreparo): int
    {
        $passos = substr_count($modoPreparo, '.') + substr_count($modoPreparo, "\n");
        return max(15, min(120, $passos * 10));
    }

    /**
     * Extrai a medida de um ingrediente
     *
     * @param string $ingrediente
     * @return string
     */
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

    /**
     * Verifica se a receita é vegetariana
     *
     * @param string $ingredientes
     * @return bool
     */
    protected function verificarVegetariano($ingredientes)
    {
        if (empty($ingredientes)) {
            return false;
        }

        $carnes = ['carne', 'frango', 'peixe', 'atum', 'bacon', 'presunto', 'salsicha', 'linguiça'];
        return !$this->contemPalavras($ingredientes, $carnes);
    }

    /**
     * Verifica se a receita é vegana
     *
     * @param string $ingredientes
     * @return bool
     */
    protected function verificarVegano($ingredientes)
    {
        if (empty($ingredientes)) {
            return false;
        }

        $naoVeganos = ['carne', 'frango', 'peixe', 'atum', 'bacon', 'presunto', 'leite', 'ovo', 'mel', 'queijo', 'manteiga', 'iogurte'];
        return !$this->contemPalavras($ingredientes, $naoVeganos);
    }

    /**
     * Verifica se a receita é sem glúten
     *
     * @param string $ingredientes
     * @return bool
     */
    protected function verificarSemGluten($ingredientes)
    {
        if (empty($ingredientes)) {
            return false;
        }

        $contemGluten = ['farinha de trigo', 'trigo', 'aveia', 'cevada', 'malte', 'centeio'];
        return !$this->contemPalavras($ingredientes, $contemGluten);
    }

    /**
     * Verifica se um texto contém alguma das palavras especificadas
     *
     * @param string $texto
     * @param array $palavras
     * @return bool
     */
    protected function contemPalavras($texto, $palavras)
    {
        $texto = strtolower($texto ?? '');
        foreach ($palavras as $palavra) {
            if (str_contains($texto, strtolower($palavra))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Formata o tempo de preparo em texto
     *
     * @param int $minutos
     * @return string
     */
    protected function formatarTempoPreparo($minutos)
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
     * Otimiza as requisições usando uma única chamada
     *
     * @param array $termos
     * @return array
     */
    public function buscarReceitasEmLote(array $termos)
    {
        try {
            $cacheKey = 'receitas_busca_lote_' . md5(implode('_', $termos));
            return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($termos) {
                try {
                    // Faz uma única requisição para a API
                    $response = Http::get("{$this->baseUrl}/receitas/todas");

                    if (!$response->successful()) {
                        Log::error('Erro ao buscar receitas em lote', [
                            'termos' => $termos,
                            'status' => $response->status(),
                            'erro' => $response->body()
                        ]);
                        return [];
                    }

                    $todasReceitas = $response->json() ?? [];
                    $resultado = [];
                    
                    if (empty($todasReceitas)) {
                        Log::warning('Nenhuma receita encontrada na API', ['termos' => $termos]);
                        return [];
                    }

                    // Para cada termo, encontra a primeira receita correspondente
                    foreach ($termos as $termo) {
                        foreach ($todasReceitas as $receita) {
                            if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                                continue;
                            }
                            
                            if (str_contains(strtolower($receita['receita']), strtolower($termo)) ||
                                str_contains(strtolower($receita['ingredientes']), strtolower($termo))) {
                                $resultado[$termo] = $this->formatarReceita($receita);
                                break;
                            }
                        }
                    }

                    return $resultado;
                } catch (\Exception $e) {
                    Log::error('Erro ao processar resposta da API em lote', [
                        'termos' => $termos,
                        'erro' => $e->getMessage()
                    ]);
                    return [];
                }
            });
        } catch (\Exception $e) {
            Log::error('Erro inesperado ao buscar receitas em lote', [
                'termos' => $termos,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Remove acentos de uma string
     *
     * @param string $string
     * @return string
     */
    protected function removerAcentos($string)
    {
        if (!is_string($string)) {
            return '';
        }
        
        $string = trim($string);
        
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
        
        return strtr(mb_strtolower($string), $map);
    }
}