<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Buscar Receitas
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('receitas.buscar') }}" class="mb-8">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <label for="termo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Digite um termo para buscar receitas
                                </label>
                                <input type="text" 
                                    name="termo" 
                                    id="termo" 
                                    placeholder="Ex: frango, arroz, feijão..."
                                    value="{{ $termo ?? '' }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white transition-colors" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full md:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(isset($erro))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $erro }}</span>
                        </div>
                    @endif

                    @if(isset($mensagem))
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $mensagem }}</span>
                        </div>
                    @endif

                    @if(empty($receitas))
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhuma receita encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Digite um termo para buscar receitas.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($receitas as $receita)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                                    <div class="relative h-48">
                                        <img src="{{ $receita['strMealThumb'] }}" alt="{{ $receita['strMeal'] }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <h3 class="absolute bottom-0 left-0 right-0 p-4 text-white font-semibold text-lg">
                                            {{ $receita['strMeal'] }}
                                        </h3>
                                    </div>

                                    <div class="p-4 space-y-4">
                                        @if(isset($receita['strCategory']))
                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                Categoria: {{ $receita['strCategory'] }}
                                            </div>
                                        @endif

                                        @if(isset($receita['strArea']))
                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Culinária: {{ $receita['strArea'] }}
                                            </div>
                                        @endif

                                        @if(isset($receita['ingredientes']) && count($receita['ingredientes']) > 0)
                                            <div class="border-t dark:border-gray-600 pt-4">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Ingredientes:</h4>
                                                <ul class="space-y-1">
                                                    @foreach($receita['ingredientes'] as $ingrediente)
                                                        <li class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            {{ $ingrediente['medida'] }} - {{ $ingrediente['nome'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <div class="pt-4 flex justify-between items-center">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-medium">Tempo:</span> {{ $receita['tempoPreparoTexto'] }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-medium">Porções:</span> {{ $receita['porcoes'] }}
                                            </div>
                                        </div>



                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 