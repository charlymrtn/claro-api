<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuraciones para Claro Pagos por ambiente
    |--------------------------------------------------------------------------
    */

    'local' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => env('CP_ADMIN', 'admin.claropay.local.com'),
            'antifraude' => env('CP_ANTIFRAUDE', 'antifraude.claropay.local.com'),
            'api' => env('CP_API', 'api.claropay.local.com'),
            'boveda' => env('CP_BOVEDA', 'boveda.claropay.local.com'),
            'clientes' => env('CP_CLIENTE', 'clientes.claropay.local.com'),
            'monitor' => env('CP_MONITOR', 'monitor.claropay.local.com'),
            'tareas' => env('CP_TAREAS', 'tareas.claropay.local.com'),
        ],

        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
            ],
        ],

    ],

    'dev' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => env('CP_ADMIN', 'atenea.dev.mavericksgateway.net'),
            'antifraude' => env('CP_ANTIFRAUDE', 'ares.dev.mavericksgateway.net'),
            'api' => env('CP_API', 'ciclope.dev.mavericksgateway.net'),
            'boveda' => env('CP_BOVEDA', 'busiris.dev.mavericksgateway.net'),
            'clientes' => env('CP_CLIENTE', 'apolo.dev.mavericksgateway.net'),
            'monitor' => env('CP_MONITOR', 'medusa.dev.mavericksgateway.net'),
            'tareas' => env('CP_TAREAS', 'triton.dev.mavericksgateway.net'),
        ],
    ],

    'qa' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => env('CP_ADMIN', 'atenea.qa.mavericksgateway.net'),
            'antifraude' => env('CP_ANTIFRAUDE', 'ares.qa.mavericksgateway.net'),
            'api' => env('CP_API', 'ciclope.qa.mavericksgateway.net'),
            'boveda' => env('CP_BOVEDA', 'busiris.qa.mavericksgateway.net'),
            'clientes' => env('CP_CLIENTE', 'apolo.qa.mavericksgateway.net'),
            'monitor' => env('CP_MONITOR', 'medusa.qa.mavericksgateway.net'),
            'tareas' => env('CP_TAREAS', 'triton.qa.mavericksgateway.net'),
        ],

    ],

    'release' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => env('CP_ADMIN', 'atenea.rel.mavericksgateway.net'),
            'antifraude' => env('CP_ANTIFRAUDE', 'ares.rel.mavericksgateway.net'),
            'api' => env('CP_API', 'ciclope.rel.mavericksgateway.net'),
            'boveda' => env('CP_BOVEDA', 'busiris.rel.mavericksgateway.net'),
            'clientes' => env('CP_CLIENTE', 'apolo.rel.mavericksgateway.net'),
            'monitor' => env('CP_MONITOR', 'medusa.rel.mavericksgateway.net'),
            'tareas' => env('CP_TAREAS', 'triton.rel.mavericksgateway.net'),
        ],

    ],

    'production' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => env('CP_ADMIN', 'atenea.prod.mavericksgateway.net'),
            'antifraude' => env('CP_ANTIFRAUDE', 'ares.prod.mavericksgateway.net'),
            'api' => env('CP_API', 'ciclope.prod.mavericksgateway.net'),
            'boveda' => env('CP_BOVEDA', 'busiris.prod.mavericksgateway.net'),
            'clientes' => env('CP_CLIENTE', 'apolo.prod.mavericksgateway.net'),
            'monitor' => env('CP_MONITOR', 'medusa.prod.mavericksgateway.net'),
            'tareas' => env('CP_TAREAS', 'triton.prod.mavericksgateway.net'),
        ],

    ],

];
