<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AlimentoRequest extends FormRequest
{
   
    private $palavrasPermitidas = [
        'fresco', 'natural', 'orgânico', 'integral', 'light', 'diet', 'zero', 'sem', 'com',
        'pedaços', 'fatias', 'inteiro', 'cortado', 'descascado', 'ralado', 'fatiado',
        'cozido', 'cru', 'assado', 'grelhado', 'defumado', 'congelado', 'resfriado',
        'maduro', 'verde', 'doce', 'azedo', 'amargo'
    ];

    /**
     * Lista de palavras não permitidas
     */
    private $palavrasBloqueadas = [
        // Objetos e materiais não comestíveis
        'papel', 'plástico', 'metal', 'vidro', 'madeira', 'tecido', 'roupa', 'sapato',
        'celular', 'computador', 'telefone', 'carro', 'moto', 'bicicleta',
        
        // Palavras ofensivas ou inadequadas
        'merda', 'bosta', 'lixo', 'porcaria', 'droga', 'cocaina', 'maconha',
        
        // Objetos domésticos
        'mesa', 'cadeira', 'sofá', 'cama', 'armário', 'gaveta', 'porta', 'janela',
        
        // Palavras genéricas não relacionadas a alimentos
        'coisa', 'negócio', 'treco', 'bagulho', 'teste', 'exemplo', 'qualquer',
        
        // Gírias e expressões informais
        'bagaça', 'muamba', 'parada', 'troço', 'trem'
    ];

    public function authorize()
    {
        return true;
    }   
    
    public function rules()
    {
        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                'min:3',
                'regex:/^[A-Za-zÀ-ú\s]+$/',
                'not_regex:/<script.*?>.*?<\/script>/i', // Proteção contra XSS
                function ($attribute, $value, $fail) {
                    // Validação do tamanho mínimo após trim
                    if (strlen(trim($value)) < 3) {
                        $fail('O nome deve ter pelo menos 3 caracteres.');
                        return;
                    }

                    // Converte para minúsculas e remove acentos para comparação
                    $nomeNormalizado = mb_strtolower($this->removerAcentos($value));
                    $palavras = explode(' ', $nomeNormalizado);

                    // Verifica palavras bloqueadas
                    foreach ($palavras as $palavra) {
                        if (in_array($palavra, array_map([$this, 'removerAcentos'], $this->palavrasBloqueadas))) {
                            $fail("O nome contém palavras não permitidas para alimentos.");
                            return;
                        }
                    }

                    // Verifica se pelo menos uma palavra está na lista de permitidas ou no mapa de categorias
                    $encontrouPalavraValida = false;
                    $mapaCategoria = $this->getMapaCategoria();
                    $todasPalavrasPermitidas = array_merge(
                        array_map([$this, 'removerAcentos'], $this->palavrasPermitidas),
                        array_map([$this, 'removerAcentos'], array_merge(...array_values($mapaCategoria)))
                    );

                    foreach ($palavras as $palavra) {
                        if (in_array($palavra, $todasPalavrasPermitidas)) {
                            $encontrouPalavraValida = true;
                            break;
                        }
                    }

                    if (!$encontrouPalavraValida) {
                        $fail("O nome não parece ser de um alimento válido. Por favor, use nomes de alimentos conhecidos.");
                    }

                    // Verifica se não tem números
                    if (preg_match('/\d/', $value)) {
                        $fail("O nome não deve conter números.");
                    }

                    // Verifica se não tem caracteres repetidos em excesso
                    if (preg_match('/(.)\1{2,}/', $value)) {
                        $fail("O nome não deve conter caracteres repetidos em excesso.");
                    }

                    // Verifica se não tem muitos espaços seguidos
                    if (preg_match('/\s{2,}/', $value)) {
                        $fail("O nome não deve conter múltiplos espaços em branco.");
                    }
                }
            ],
            'tipo_quantidade' => [
                'required',
                'in:unidade,quilo,litro',
                function ($attribute, $value, $fail) {
                    $categoria = \App\Models\Categoria::find($this->categoria_id);
                    if (!$categoria) return;

                    $categoriaNome = strtolower($categoria->nome);
                    $nomeNormalizado = mb_strtolower($this->removerAcentos($this->nome));
                    
                    // Validações específicas por categoria
                    switch ($categoriaNome) {
                        case 'bebidas':
                            if ($value !== 'litro' && $value !== 'unidade') {
                                $fail('Bebidas só podem ser medidas em litros ou unidades.');
                            }
                            break;
                        case 'frutas':
                        case 'verduras':
                        case 'legumes':
                            if ($value === 'litro') {
                                $fail("$categoria->nome não podem ser medidos em litros.");
                            }
                            break;
                        case 'carnes':
                            if ($value === 'litro') {
                                $fail('Carnes não podem ser medidas em litros.');
                            }
                            break;
                        case 'laticínios':
                            if ($value === 'litro') {
                                $fail('Laticínios líquidos devem ser medidos em litros.');
                            }
                            break;
                        case 'congelados':
                            if ($value === 'litro') {
                                $fail('Alimentos congelados não podem ser medidos em litros.');
                            }
                            break;
                    }

                    // Validação para litros
                    if ($value === 'litro') {
                        $palavrasLiquidas = ['leite', 'suco', 'agua', 'refrigerante', 'vinho', 'cerveja', 'oleo', 'azeite', 'vinagre', 'iogurte'];
                        $ehLiquido = false;

                        foreach ($palavrasLiquidas as $palavra) {
                            if (str_contains($nomeNormalizado, $palavra)) {
                                $ehLiquido = true;
                                break;
                            }
                        }

                        if (!$ehLiquido) {
                            $fail('Este alimento não parece ser um líquido. A medida em litros só é adequada para bebidas e alimentos líquidos.');
                        }
                    }

                    // Validação para quilos
                    if ($value === 'quilo') {
                        $palavrasQuilo = [
                            'arroz', 'feijao', 'acucar', 'sal', 'farinha', 'cafe', 'queijo', 
                            'carne', 'frango', 'peixe', 'batata', 'cebola', 'tomate', 'cenoura', 
                            'banana', 'maca', 'laranja', 'alface', 'repolho', 'couve', 'brocolis', 
                            'espinafre', 'rucula', 'agriao', 'acelga', 'alho', 'pepino', 'abobrinha', 
                            'berinjela', 'pimentao', 'milho', 'ervilha', 'vagem', 'mandioca', 'inhame', 
                            'batata-doce', 'beterraba', 'rabanete', 'nabo'
                        ];
                        $ehPesoValido = false;

                        foreach ($palavrasQuilo as $palavra) {
                            if (str_contains($nomeNormalizado, $palavra)) {
                                $ehPesoValido = true;
                                break;
                            }
                        }

                        // Se for da categoria congelados, permite medição em quilos
                        if ($categoriaNome === 'congelados') {
                            $ehPesoValido = true;
                        }

                        if (!$ehPesoValido) {
                            $fail('Este alimento não é comumente medido em quilos. Use quilos apenas para alimentos vendidos por peso (como grãos, carnes, frutas, verduras).');
                        }
                    }

                    // Validação para unidades
                    if ($value === 'unidade') {
                        $palavrasUnidade = ['pao', 'pacote', 'lata', 'garrafa', 'caixa', 'pote', 'sache', 'ovo', 'maca', 'banana', 'laranja', 'limao', 'biscoito', 'bolacha', 'chocolate', 'barra', 'iogurte', 'leite'];
                        $ehUnidadeValida = false;

                        foreach ($palavrasUnidade as $palavra) {
                            if (str_contains($nomeNormalizado, $palavra)) {
                                $ehUnidadeValida = true;
                                break;
                            }
                        }

                        // Se for da categoria congelados, permite medição em unidades
                        if ($categoriaNome === 'congelados') {
                            $ehUnidadeValida = true;
                        }

                        if (!$ehUnidadeValida) {
                            $fail('Este alimento não é comumente medido em unidades. Use unidades para itens contáveis (como pães, ovos, frutas individuais, pacotes, latas).');
                        }
                    }
                }
            ],
            'quantidade' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $categoria = \App\Models\Categoria::find($this->categoria_id);
                    if (!$categoria) return;

                    $categoriaNome = strtolower($categoria->nome);
                    
                    switch ($this->tipo_quantidade) {
                        case 'unidade':
                            if (!is_int($value * 1)) {
                                $fail('A quantidade em unidades deve ser um número inteiro.');
                            }
                            if ($value > 999) {
                                $fail('A quantidade máxima permitida é 999 unidades.');
                            }
                            break;
                        case 'quilo':
                            if ($value > 100) {
                                $fail('A quantidade máxima permitida é 100 quilos.');
                            }
                            // Validações específicas por categoria para quilos
                            switch ($categoriaNome) {
                                case 'carnes':
                                    if ($value > 50) {
                                        $fail('A quantidade máxima de carne permitida é 50 quilos.');
                                    }
                                    break;
                                case 'frutas':
                                case 'verduras':
                                case 'legumes':
                                    if ($value > 50) {
                                        $fail("A quantidade máxima de $categoria->nome permitida é 50 quilos.");
                                    }
                                    break;
                            }
                            break;
                        case 'litro':
                            if ($value > 50) {
                                $fail('A quantidade máxima permitida é 50 litros.');
                            }
                            // Validações específicas por categoria para litros
                            if ($categoriaNome === 'bebidas') {
                                if ($value > 20) {
                                    $fail('A quantidade máxima de bebidas permitida é 20 litros.');
                                }
                            }
                            break;
                    }
                }
            ],
            'validade' => [
                'required',
                'date',
                'after_or_equal:today',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    $validade = \Carbon\Carbon::parse($value);
                    $umAnoDepois = \Carbon\Carbon::now()->addYear();
                    $umaSemanaAntes = \Carbon\Carbon::now()->subWeek();
                    
                    if ($validade->gt($umAnoDepois)) {
                        $fail('A data de validade não pode ser superior a um ano.');
                    }
                    
                    if ($validade->lt($umaSemanaAntes)) {
                        $fail('A data de validade não pode ser anterior a uma semana da data atual.');
                    }

                    // Validações específicas por categoria para validade
                    $categoria = \App\Models\Categoria::find($this->categoria_id);
                    if (!$categoria) return;

                    $categoriaNome = strtolower($categoria->nome);
                    $hoje = \Carbon\Carbon::now();
                    
                    switch ($categoriaNome) {
                        case 'frutas':
                        case 'verduras':
                        case 'legumes':
                            $umMesDepois = $hoje->copy()->addMonth();
                            if ($validade->gt($umMesDepois)) {
                                $fail("A validade máxima para {$categoria->nome} é de 1 mês.");
                            }
                            break;
                        case 'carnes':
                            if (!str_contains(strtolower($this->nome), 'congelad')) {
                                $duasSemanas = $hoje->copy()->addWeeks(2);
                                if ($validade->gt($duasSemanas)) {
                                    $fail('A validade máxima para carnes não congeladas é de 2 semanas.');
                                }
                            }
                            break;
                        case 'laticínios':
                            $doisMeses = $hoje->copy()->addMonths(2);
                            if ($validade->gt($doisMeses)) {
                                $fail('A validade máxima para laticínios é de 2 meses.');
                            }
                            break;
                    }
                }
            ],
            'categoria_id' => [
                'required',
                'exists:categorias,id',
                function ($attribute, $value, $fail) {
                    $nome = strtolower($this->nome);
                    $categoria = \App\Models\Categoria::find($value);
                    
                    if (!$categoria) return;
                    
                    $mapaCategoria = $this->getMapaCategoria();

                    $categoriaNome = strtolower($categoria->nome);
                    if (isset($mapaCategoria[$categoriaNome])) {
                        $permitidos = $mapaCategoria[$categoriaNome];
                        $encontrou = false;

                        foreach ($permitidos as $palavra) {
                            if (str_contains($nome, strtolower($palavra))) {
                                $encontrou = true;
                                break;
                            }
                        }

                        if (!$encontrou) {
                            $fail("O nome do alimento não condiz com a categoria \"{$categoria->nome}\". Verifique se o nome está correto ou selecione outra categoria.");
                        }
                    }
                }
            ],
            'sugestao' => [
                'nullable',
                'string',
                'max:255',
                'not_regex:/<script.*?>.*?<\/script>/i',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        // Verifica caracteres especiais suspeitos
                        if (preg_match('/[<>{}$]/', $value)) {
                            $fail('A sugestão contém caracteres não permitidos.');
                        }
                        
                        // Verifica URLs maliciosas
                        if (preg_match('/(javascript|data):/i', $value)) {
                            $fail('A sugestão contém conteúdo não permitido.');
                        }
                    }
                }
            ]
        ];
    }

    /**
     * Remove acentos de uma string
     */
    private function removerAcentos($string) {
        return strtolower(preg_replace(
            ['/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u'],
            ['a', 'e', 'i', 'o', 'u', 'c'],
            mb_strtolower($string)
        ));
    }

    /**
     * Retorna o mapa de categorias e seus alimentos permitidos
     */
    private function getMapaCategoria() {
        return [
            'frutas' => ['maçã', 'banana', 'melancia', 'limão', 'laranja', 'manga', 'uva', 'abacaxi', 'goiaba', 'morango', 'kiwi', 'pera', 'pêssego', 'ameixa', 'caju', 'graviola', 'acerola', 'framboesa', 'maracujá', 'figo', 'mamão', 'abacate', 'coco', 'romã', 'pitaya', 'carambola'],
            'verduras' => ['alface', 'rúcula', 'espinafre', 'couve', 'agrião', 'repolho', 'acelga', 'radite', 'mostarda', 'almeirão', 'endívia', 'chicória', 'escarola', 'ervilha', 'brócolis', 'couve-flor', 'salsa', 'cebolinha', 'hortelã', 'manjericão'],
            'legumes' => ['cenoura', 'batata', 'abobrinha', 'pepino', 'chuchu', 'berinjela', 'beterraba', 'mandioca', 'inhame', 'cará', 'abóbora', 'pimentão', 'tomate', 'milho', 'quiabo', 'vagem', 'nabo', 'rabanete', 'gengibre', 'alho'],
            'carnes' => ['carne', 'bovina', 'porco', 'lombo', 'frango', 'filé', 'picanha', 'costela', 'moída', 'linguiça', 'pernil', 'alcatra', 'maminha', 'peito', 'coxinha', 'coxa', 'tilápia', 'salmão', 'atum', 'peixe', 'bacalhau', 'sardinha', 'merluza', 'pescada', 'camarão'],
            'bebidas' => ['água', 'refrigerante', 'suco', 'cerveja', 'vinho', 'chá', 'café', 'achocolatado', 'milkshake', 'isotônico', 'energético', 'licor', 'leite', 'vodka', 'rum', 'whisky', 'champagne', 'coquetel', 'aguardente', 'sidra'],
            'laticínios' => ['leite', 'queijo', 'iogurte', 'requeijão', 'manteiga', 'margarina', 'cream cheese', 'nata', 'creme de leite', 'leite condensado', 'muçarela', 'parmesão', 'ricota', 'cottage', 'coalhada'],
            'grãos' => ['arroz', 'feijão', 'milho', 'soja', 'lentilha', 'grão de bico', 'ervilha', 'amendoim', 'quinoa', 'chia', 'linhaça', 'gergelim', 'aveia', 'cevada', 'trigo'],
            'congelados' => ['lasanha', 'pizza', 'hambúrguer', 'nuggets', 'pão de queijo', 'legumes', 'polpa', 'sorvete', 'açaí', 'batata frita', 'empanado', 'massa', 'yakisoba', 'peixe', 'camarão']
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome do alimento é obrigatório.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome não pode ter mais que :max caracteres.',
            'nome.min' => 'O nome deve ter pelo menos :min caracteres.',
            'nome.regex' => 'O nome deve conter apenas letras e espaços.',
            'tipo_quantidade.required' => 'O tipo de quantidade é obrigatório.',
            'tipo_quantidade.in' => 'O tipo de quantidade deve ser unidade, quilo ou litro.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.numeric' => 'A quantidade deve ser um número.',
            'quantidade.min' => 'A quantidade deve ser maior que :min.',
            'validade.required' => 'A data de validade é obrigatória.',
            'validade.date' => 'A data de validade deve ser uma data válida.',
            'validade.after' => 'A data de validade deve ser posterior a hoje.',
            'categoria_id.required' => 'A categoria é obrigatória.',
            'categoria_id.exists' => 'A categoria selecionada não existe.',
            'sugestao.max' => 'A sugestão não pode ter mais que :max caracteres.',
            'sugestao.not_regex' => 'A sugestão contém conteúdo não permitido.'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->nome) {
            $this->merge([
                'nome' => ucwords(strtolower(trim($this->nome)))
            ]);
        }
    }
}
