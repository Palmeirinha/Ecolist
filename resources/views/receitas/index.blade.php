@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-semibold mb-6">Receitas</h1>

                <!-- Formulário de Busca -->
                <div class="mb-8">
                    <form action="{{ route('receitas.buscar') }}" method="GET" class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" 
                                   name="termo" 
                                   placeholder="Busque por receitas..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                   value="{{ request('termo') }}">
                        </div>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Sugestões baseadas nos seus ingredientes -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Sugestões baseadas nos seus ingredientes</h2>
                    <a href="{{ route('receitas.sugestoes') }}" 
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Ver Sugestões
                    </a>
                </div>

                <!-- Receitas Populares -->
                <div>
                    <h2 class="text-xl font-semibold mb-4">Receitas Populares</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Placeholder para receitas populares -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500">Carregando receitas populares...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 