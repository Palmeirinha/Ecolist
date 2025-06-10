<x-app-layout>
    {{-- Cabeçalho da página com título e botões de ação --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Meus Alimentos
            </h2>
            <div class="flex gap-4">
                {{-- Botão para ver sugestões de receitas --}}
                <a href="{{ route('alimentos.receitas') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Ver Sugestões de Receitas
                </a>
                {{-- Botão para adicionar um novo alimento --}}
                <a href="{{ route('alimentos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Adicionar Alimento
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Área principal da página --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filtro para buscar alimentos por categoria --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" action="{{ route('alimentos.index') }}" class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-[200px]">
                        <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filtrar por categoria</label>
                        <select name="categoria_id" id="categoria_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white">
                            <option value="">Todas as categorias</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ (isset($categoriaId) && $categoriaId == $categoria->id) ? 'selected' : '' }}>
                                    {{ $categoria->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-none">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Exibe mensagem caso não haja alimentos cadastrados --}}
            @if($alimentos->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhum alimento cadastrado</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece cadastrando seu primeiro alimento.</p>
                    <div class="mt-6">
                        <a href="{{ route('alimentos.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Cadastrar Alimento
                        </a>
                    </div>
                </div>
            @else
                {{-- Lista de alimentos cadastrados --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($alimentos as $alimento)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <div class="flex flex-col space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $alimento->nome }}</h3>
                                    {{-- Exibe a data de validade com cor de acordo com o status --}}
                                    <span class="px-3 py-1 text-sm rounded-full {{ $alimento->vencido ? 'bg-red-100 text-red-800' : ($alimento->vencendo ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $alimento->validade->format('d/m/Y') }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    {{-- Exibe quantidade e unidade de medida --}}
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                        </svg>
                                        Quantidade: {{ $alimento->quantidade }} {{ $alimento->unidade_medida }}
                                    </div>

                                    {{-- Exibe a categoria do alimento --}}
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        Categoria: {{ $alimento->categoria->nome }}
                                    </div>

                                    {{-- Exibe sugestão de receita, se houver --}}
                                    @if($alimento->sugestao)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Sugestão: {{ $alimento->sugestao }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Botões para editar ou excluir o alimento --}}
                                <div class="flex justify-end space-x-4 mt-4">
                                    <a href="{{ route('alimentos.edit', $alimento->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </a>
                                    <form action="{{ route('alimentos.destroy', $alimento->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir o alimento \'{{ $alimento->nome }}\'?\nEsta ação não poderá ser desfeita.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Script para submeter o filtro automaticamente ao trocar a categoria --}}
    <script>
        document.getElementById('categoria_id').addEventListener('change', function() {
            this.closest('form').submit();
        });
    </script>
</x-app-layout>
