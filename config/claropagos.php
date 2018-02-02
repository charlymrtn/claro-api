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
            'admin' => [
                'url' => env('CP_ADMIN', 'admin.claropay.local.com'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'antifraude.claropay.local.com'),
            ],
            'api' => [
                'url' => env('CP_API', 'api.claropay.local.com'),
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ'
                         . '0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZW'
                         . 'YzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiO'
                         . 'jE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRt'
                         . 'aW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3'
                         . 'AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0 '
                         . 'CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyA'
                         . 'LyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW'
                         . '6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLR'
                         . 'Yk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_8'
                         . '0XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc',
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'boveda.claropay.local.com'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'clientes.claropay.local.com'),
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'monitor.claropay.local.com'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'tareas.claropay.local.com'),
            ],
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
            'admin' => [
                'url' => env('CP_ADMIN', 'atenea.dev.mavericksgateway.net'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'ares.dev.mavericksgateway.net'),
            ],
            'api' => [
                'url' => env('CP_API', 'ciclope.dev.mavericksgateway.net'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'busiris.dev.mavericksgateway.net'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'apolo.dev.mavericksgateway.net'),
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'medusa.dev.mavericksgateway.net'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'triton.dev.mavericksgateway.net'),
            ],
        ],
    ],

    'qa' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => [
                'url' => env('CP_ADMIN', 'atenea.qa.mavericksgateway.net'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'ares.qa.mavericksgateway.net'),
            ],
            'api' => [
                'url' => env('CP_API', 'ciclope.qa.mavericksgateway.net'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'busiris.qa.mavericksgateway.net'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'apolo.qa.mavericksgateway.net'),
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'medusa.qa.mavericksgateway.net'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'triton.qa.mavericksgateway.net'),
            ],
        ],

    ],

    'release' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => [
                'url' => env('CP_ADMIN', 'atenea.rel.mavericksgateway.net'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'ares.rel.mavericksgateway.net'),
            ],
            'api' => [
                'url' => env('CP_API', 'ciclope.rel.mavericksgateway.net'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'busiris.rel.mavericksgateway.net'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'apolo.rel.mavericksgateway.net'),
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'medusa.rel.mavericksgateway.net'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'triton.rel.mavericksgateway.net'),
            ],
        ],

    ],

    'sandbox' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => [
                'url' => env('CP_ADMIN', 'admin.sandbox.claropagos.com'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'antifraude.sandbox.claropagos.com'),
            ],
            'api' => [
                'url' => env('CP_API', 'api.sandbox.claropagos.com'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'boveda.sandbox.claropagos.com'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'clientes.sandbox.claropagos.com'),
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'monitor.sandbox.claropagos.com'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'tareas.sandbox.claropagos.com'),
            ],
        ],

    ],

    'production' => [

        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => [
                'url' => env('CP_ADMIN', 'admin.claropagos.com'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'antifraude.claropagos.com'),
            ],
            'api' => [
                'url' => env('CP_API', 'api.claropagos.com'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'boveda.claropagos.com'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'clientes.claropagos.com'),
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'monitor.claropagos.com'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'tareas.claropagos.com'),
            ],
        ],

    ],

];
