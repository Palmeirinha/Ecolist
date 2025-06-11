<x-app-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-b from-primary-50 to-white px-4">
        <div class="text-center max-w-4xl mx-auto">
            <div class="mb-8">
                <img src="/images/logo.svg" alt="EcoList" class="w-24 h-24 mx-auto mb-6">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Bem-vindo ao <span class="text-primary-600">Ecolist</span> 🌿
                </h1>
                <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                    Gerencie seus alimentos, evite desperdício e receba sugestões de receitas com o que você já tem em casa.
                </p>
            </div>

            {{-- Botões principais de ação --}}
            <div class="space-y-4 md:space-y-0 md:space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Acessar Painel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-white hover:bg-gray-50 text-primary-600 font-medium rounded-lg border-2 border-primary-600 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Criar Conta
                    </a>
                @endauth
            </div>

            <!-- Funcionalidades principais do sistema -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto px-4">
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="bg-primary-100 rounded-full w-12 h-12 flex items-center justify-center mb-4 mx-auto">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Organize seus Alimentos</h3>
                    <p class="text-gray-600 text-center">Mantenha um controle eficiente dos alimentos em sua casa.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="bg-primary-100 rounded-full w-12 h-12 flex items-center justify-center mb-4 mx-auto">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Evite Desperdício</h3>
                    <p class="text-gray-600 text-center">Controle a validade dos seus alimentos de forma eficiente.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="bg-primary-100 rounded-full w-12 h-12 flex items-center justify-center mb-4 mx-auto">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Descubra Receitas</h3>
                    <p class="text-gray-600 text-center">Encontre receitas baseadas nos ingredientes disponíveis.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
