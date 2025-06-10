<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="EcoList - Gerencie seus alimentos e evite desperdício">
        <meta name="theme-color" content="#16a34a">

        {{-- Nome da aplicação --}}
        <title>{{ config('app.name', 'EcoList') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- PWA -->
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/icon-192x192.png">

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
    <body class="font-sans antialiased min-h-full bg-gray-50">
        {{-- Container principal --}}
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 py-6">
                {{-- Mensagens de feedback --}}
                @if (session('success'))
                    <div class="max-w-7xl mx-auto mt-4 px-4">
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <!-- Exibe mensagem de erro, caso exista na sessão -->
                @if (session('error'))
                    <div class="max-w-7xl mx-auto mt-4 px-4">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <!-- Área principal onde o conteúdo das páginas será exibido -->
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Scripts para funcionalidades extras -->
        <script>
            // Registra o Service Worker para transformar o site em um PWA (aplicativo instalável)
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(registration => {
                            console.log('ServiceWorker registrado com sucesso:', registration);
                        })
                        .catch(error => {
                            console.log('Falha ao registrar ServiceWorker:', error);
                        });
                });
            }
        </script>

        <!-- Permite empilhar scripts adicionais de outras views -->
        @stack('scripts')
    </body>
</html>
