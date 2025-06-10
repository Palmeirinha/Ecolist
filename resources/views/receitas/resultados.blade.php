@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold">Resultados da Busca</h1>
                    <a href="{{ route('receitas.index') }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Voltar
                    </a>
                </div>

                <!-- Formulário de Busca -->
                <div class="mb-8">
                    <form action="{{ route('receitas.buscar') }}" method="GET" class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" 
                                   name="termo" 
                                   placeholder="Busque por receitas..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                   value="{{ $termo ?? '' }}">
                        </div>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Resultados da Busca -->
                <div>
                    @if(isset($receitas) && count($receitas) > 0)
                        <h2 class="text-xl font-semibold mb-4">Receitas encontradas para "{{ $termo ?? 'Busca' }}"</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($receitas as $receita)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                    @if($receita->imagem)
                                        <img src="{{ $receita->imagem }}" 
                                             alt="{{ $receita->titulo }}" 
                                             class="w-full h-48 object-cover">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-400">Sem imagem</span>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold mb-2">{{ $receita->titulo }}</h3>
                                        <p class="text-gray-600 text-sm mb-4">
                                            {{ Str::limit($receita->descricao, 100) }}
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">
                                                Tempo: {{ $receita->tempo_preparo ?? 'N/A' }}
                                            </span>
                                            <a href="#" 
                                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                Ver Receita
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-lg">
                                Nenhuma receita encontrada para "{{ $termo ?? 'Busca' }}".
                            </p>
                            <p class="text-gray-400 mt-2">
                                Tente buscar com termos diferentes ou verifique a ortografia.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 