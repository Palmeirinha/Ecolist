{{-- Tela de edição de um alimento já cadastrado --}}

<x-app-layout>
    {{-- Cabeçalho da página com título e botão de voltar --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editar Alimento
            </h2>
            <a href="{{ route('alimentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200">
                {{-- Ícone de voltar --}}
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Voltar
            </a>
        </div>
    </x-slot>

    {{-- Conteúdo principal da página --}}
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    {{-- Formulário para editar o alimento --}}
                    <form action="{{ route('alimentos.update', $alimento) }}" method="POST" id="formAlimento" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        {{-- Campo para nome do alimento --}}
                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do alimento</label>
                            <div class="mt-1">
                                <input type="text" name="nome" id="nome" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white transition-colors @error('nome') border-red-500 @enderror"
                                    value="{{ old('nome', $alimento->nome) }}" required>
                                {{-- Exibe mensagem de erro se houver --}}
                                @error('nome')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Campos para tipo de quantidade e quantidade --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tipo_quantidade" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de quantidade</label>
                                <div class="mt-1">
                                    <select name="tipo_quantidade" id="tipo_quantidade" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white transition-colors @error('tipo_quantidade') border-red-500 @enderror"
                                        onchange="atualizaLimite()">
                                        <option value="unidade" {{ old('tipo_quantidade', $alimento->tipo_quantidade) == 'unidade' ? 'selected' : '' }}>Unidade</option>
                                        <option value="quilo" {{ old('tipo_quantidade', $alimento->tipo_quantidade) == 'quilo' ? 'selected' : '' }}>Quilo</option>
                                        <option value="litro" {{ old('tipo_quantidade', $alimento->tipo_quantidade) == 'litro' ? 'selected' : '' }}>Litro</option>
                                    </select>
                                    @error('tipo_quantidade')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="quantidade" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Quantidade
                                    <span id="quantidadeInfo" class="text-gray-500 dark:text-gray-400 text-xs"></span>
                                </label>
                                <div class="mt-1">
                                    <input type="number" name="quantidade" id="quantidade" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white transition-colors @error('quantidade') border-red-500 @enderror"
                                        value="{{ old('quantidade', $alimento->quantidade) }}"
                                        min="1" step="0.1" required>
                                    @error('quantidade')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Campo para data de validade --}}
                        <div>
                            <label for="validade" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de validade</label>
                            <div class="mt-1">
                                <input type="date" name="validade" id="validade" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white transition-colors @error('validade') border-red-500 @enderror"
                                    value="{{ old('validade', $alimento->validade) }}" required min="{{ date('Y-m-d') }}">
                                @error('validade')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Campo para selecionar a categoria do alimento --}}
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoria</label>
                            <div class="mt-1">
                                <select name="categoria_id" id="categoria_id" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white transition-colors @error('categoria_id') border-red-500 @enderror"
                                    required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}"
                                            {{ (old('categoria_id', $alimento->categoria_id) == $categoria->id) ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Botões de cancelar e salvar alterações --}}
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" onclick="window.history.back()"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" id="btnSalvar"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                {{-- Ícone de salvar --}}
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script para validação dinâmica dos campos do formulário --}}
    <script>
        const form = document.getElementById('formAlimento');
        const btnSalvar = document.getElementById('btnSalvar');
        const quantidadeInput = document.getElementById('quantidade');
        const quantidadeInfo = document.getElementById('quantidadeInfo');
        const tipoQuantidadeSelect = document.getElementById('tipo_quantidade');
        const nomeInput = document.getElementById('nome');

        // Função que ajusta limites e validações conforme o tipo e categoria do alimento
        function atualizaLimite() {
            const tipo = tipoQuantidadeSelect.value;
            const nome = nomeInput.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const categoria = document.getElementById('categoria_id').value;
            const categoriaText = document.getElementById('categoria_id').options[document.getElementById('categoria_id').selectedIndex].text.toLowerCase();
            
            // Remove mensagem de erro anterior
            const mensagemErroExistente = tipoQuantidadeSelect.parentElement.querySelector('.text-red-600');
            if (mensagemErroExistente) {
                mensagemErroExistente.remove();
            }
            tipoQuantidadeSelect.classList.remove('border-red-500');
            
            // Ajusta step e min do campo quantidade conforme o tipo selecionado
            if (tipo === 'unidade') {
                quantidadeInput.step = '1';
                quantidadeInput.min = '1';
                quantidadeInfo.textContent = '(apenas números inteiros)';
            } else {
                quantidadeInput.step = '0.1';
                quantidadeInput.min = '0.1';
                quantidadeInfo.textContent = tipo === 'quilo' ? '(em kg)' : '(em L)';
            }

            // Validações específicas para cada categoria
            switch (categoriaText) {
                case 'bebidas':
                    if (tipo !== 'litro' && tipo !== 'unidade') {
                        tipoQuantidadeSelect.classList.add('border-red-500');
                        const mensagem = document.createElement('p');
                        mensagem.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        mensagem.textContent = 'Bebidas só podem ser medidas em litros ou unidades.';
                        tipoQuantidadeSelect.parentElement.appendChild(mensagem);
                    }
                    break;
                case 'frutas':
                case 'verduras':
                case 'legumes':
                    if (tipo === 'litro') {
                        tipoQuantidadeSelect.classList.add('border-red-500');
                        const mensagem = document.createElement('p');
                        mensagem.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        mensagem.textContent = `${categoriaText} não podem ser medidos em litros.`;
                        tipoQuantidadeSelect.parentElement.appendChild(mensagem);
                    }
                    break;
                case 'carnes':
                    if (tipo === 'litro') {
                        tipoQuantidadeSelect.classList.add('border-red-500');
                        const mensagem = document.createElement('p');
                        mensagem.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        mensagem.textContent = 'Carnes não podem ser medidas em litros.';
                        tipoQuantidadeSelect.parentElement.appendChild(mensagem);
                    }
                    break;
                case 'laticínios':
                    if (tipo === 'litro') {
                        tipoQuantidadeSelect.classList.add('border-red-500');
                        const mensagem = document.createElement('p');
                        mensagem.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        mensagem.textContent = 'Laticínios líquidos devem ser medidos em litros.';
                        tipoQuantidadeSelect.parentElement.appendChild(mensagem);
                    }
                    break;
                case 'congelados':
                    if (tipo === 'litro') {
                        tipoQuantidadeSelect.classList.add('border-red-500');
                        const mensagem = document.createElement('p');
                        mensagem.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        mensagem.textContent = 'Alimentos congelados não podem ser medidos em litros.';
                        tipoQuantidadeSelect.parentElement.appendChild(mensagem);
                    }
                    break;
            }

            // Validação extra para evitar que alimentos não líquidos sejam cadastrados em litros
            if (tipo === 'litro' && !['bebidas', 'laticínios'].includes(categoriaText)) {
                const palavrasLiquidas = ['leite', 'suco', 'agua', 'refrigerante', 'vinho', 'cerveja', 'oleo', 'azeite', 'vinagre', 'iogurte'];
                let ehLiquido = false;

                for (const palavra of palavrasLiquidas) {
                    if (nome.includes(palavra)) {
                        ehLiquido = true;
                        break;
                    }
                }

                if (!ehLiquido && nome.length > 0) {
                    tipoQuantidadeSelect.classList.add('border-red-500');
                    const mensagem = document.createElement('p');
                    mensagem.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                    mensagem.textContent = 'Este alimento não parece ser um líquido. A medida em litros só é adequada para bebidas e alimentos líquidos.';
                    tipoQuantidadeSelect.parentElement.appendChild(mensagem);
                }
            }
        }

        // Atualiza o limite inicial ao carregar a página
        atualizaLimite();

        // Atualiza limites e validações ao mudar tipo de quantidade ou nome
        tipoQuantidadeSelect.addEventListener('change', atualizaLimite);
        nomeInput.addEventListener('input', atualizaLimite);

        // Valida o formulário antes de enviar e exibe loading no botão
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                return false;
            }
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Salvando...
            `;
        });
    </script>
</x-app-layout>

