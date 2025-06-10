<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissões do Sistema
    |--------------------------------------------------------------------------
    |
    | Lista de todas as permissões disponíveis no sistema.
    | Cada permissão deve ter uma descrição clara do que permite fazer.
    |
    */

    'permissions' => [
        'manage-alimentos' => [
            'name' => 'Gerenciar Alimentos',
            'description' => 'Permite criar, editar, visualizar e excluir alimentos',
            'roles' => ['admin', 'user']
        ],
        'manage-categorias' => [
            'name' => 'Gerenciar Categorias',
            'description' => 'Permite criar, editar, visualizar e excluir categorias',
            'roles' => ['admin']
        ],
        'view-dashboard' => [
            'name' => 'Visualizar Dashboard',
            'description' => 'Permite visualizar o dashboard com estatísticas',
            'roles' => ['admin', 'user']
        ],
        'manage-receitas' => [
            'name' => 'Gerenciar Receitas',
            'description' => 'Permite buscar e visualizar receitas',
            'roles' => ['admin', 'user']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Papéis (Roles)
    |--------------------------------------------------------------------------
    |
    | Define os papéis disponíveis no sistema e suas permissões padrão.
    |
    */

    'roles' => [
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Acesso total ao sistema',
            'permissions' => [
                'manage-alimentos',
                'manage-categorias',
                'view-dashboard',
                'manage-receitas'
            ]
        ],
        'user' => [
            'name' => 'Usuário',
            'description' => 'Acesso básico ao sistema',
            'permissions' => [
                'manage-alimentos',
                'view-dashboard',
                'manage-receitas'
            ]
        ]
    ]
]; 