<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuraciones para Claro Pagos por ambiente
    |--------------------------------------------------------------------------
    */

    'local' => [
        // El ambiente cuenta con sandbox
        'sandbox' => false,
        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => [
                'url' => env('CP_ADMIN', 'http://admin.claropay.local.com'),
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImZlMjg1M2Y5Y2UxOTgxY2Y0NWM4MGY0ODQ0MDU5M2EyMzJjYzg4ZmI'
                         . '3ZWIzYmViY2U2ODM2NjNiMWEyN2IyMzI3ZWI1YjVmOTBiYTBhYjdmIn0.eyJhdWQiOiIxIiwianRpIjoiZmUyODUzZjljZTE5OD'
                         . 'FjZjQ1YzgwZjQ4NDQwNTkzYTIzMmNjODhmYjdlYjNiZWJjZTY4MzY2M2IxYTI3YjIzMjdlYjViNWY5MGJhMGFiN2YiLCJpYXQiO'
                         . 'jE1MjA3NTE0ODcsIm5iZiI6MTUyMDc1MTQ4NywiZXhwIjoxNTUyMjg3NDg3LCJzdWIiOiIiLCJzY29wZXMiOlsic3VwZXJhZG1p'
                         . 'biJdfQ.bZ7RtLMT2WX9bPyGNM9GKgJE4Stwza1cYS1IjQGMN6K4cXZjyq1Rht0dOEcXlmUOne3anYxDziGvc81FY1ENf0M9nzpa'
                         . '6xS5B8t8YlZNawezAG6Ll7DkGW6GYXqe1_WC8Y1zAdp28-laTfJAiX5I8cm-oVXo4FhVhcex1u9OmUBvRor_chWlbdQE90GItbm'
                         . 'UHUOYAh0hajmG-tLQ_oMUBh0tiiDVH9CFylYNx3lSPZKnFNGdtUqoky1jTaiFwUvf4ZVsZ3Bww6ZoVsFR67LdCIUIzlxJ7TvWnW'
                         . 'sj8yDzwcbp2wF8guOeaE9QiwfoE8roWlVsigcL_3x3quilAtudLMiKSk9BW49T9qfZBGmHUXyBZTQUf2cUP2Fz2csMol62FhHYr'
                         . '0PjtMnphUqhpmqHm1Ay7q5W4LPrfrID9SA8BSyrnZH8AWVOv-QSMiKyKyCVTmIZOSP9VrogngdsAu9RQi9U3ilyNo8GZUF1_k6G'
                         . 'rYANYXnsljBalHGx1XN9vm6H3tRviXcVWuFvnyGUqIhIGofH6II-vN8eGKFC72pTg4zffiWD-jppjSGTPUYYqaqV53pmA5V9dFG'
                         . '_bpfj1gMHrd4V3DTKgx46WWxvNslAbQrQelSt5Ut107YUY7Wazhc31j9tc6iwc_dPhjuCQ5F19whLINb6hQBjtBCEYtjFsS4',
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'http://antifraude.claropay.local.com'),
            ],
            'api' => [
                'url' => env('CP_API', 'http://api.claropay.local.com'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'http://boveda.claropay.local.com'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'http://clientes.claropay.local.com'),
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImZlMjg1M2Y5Y2UxOTgxY2Y0NWM4MGY0ODQ0MDU5M2EyMzJjYzg4ZmI'
                         . '3ZWIzYmViY2U2ODM2NjNiMWEyN2IyMzI3ZWI1YjVmOTBiYTBhYjdmIn0.eyJhdWQiOiIxIiwianRpIjoiZmUyODUzZjljZTE5OD'
                         . 'FjZjQ1YzgwZjQ4NDQwNTkzYTIzMmNjODhmYjdlYjNiZWJjZTY4MzY2M2IxYTI3YjIzMjdlYjViNWY5MGJhMGFiN2YiLCJpYXQiO'
                         . 'jE1MjA3NTE0ODcsIm5iZiI6MTUyMDc1MTQ4NywiZXhwIjoxNTUyMjg3NDg3LCJzdWIiOiIiLCJzY29wZXMiOlsic3VwZXJhZG1p'
                         . 'biJdfQ.bZ7RtLMT2WX9bPyGNM9GKgJE4Stwza1cYS1IjQGMN6K4cXZjyq1Rht0dOEcXlmUOne3anYxDziGvc81FY1ENf0M9nzpa'
                         . '6xS5B8t8YlZNawezAG6Ll7DkGW6GYXqe1_WC8Y1zAdp28-laTfJAiX5I8cm-oVXo4FhVhcex1u9OmUBvRor_chWlbdQE90GItbm'
                         . 'UHUOYAh0hajmG-tLQ_oMUBh0tiiDVH9CFylYNx3lSPZKnFNGdtUqoky1jTaiFwUvf4ZVsZ3Bww6ZoVsFR67LdCIUIzlxJ7TvWnW'
                         . 'sj8yDzwcbp2wF8guOeaE9QiwfoE8roWlVsigcL_3x3quilAtudLMiKSk9BW49T9qfZBGmHUXyBZTQUf2cUP2Fz2csMol62FhHYr'
                         . '0PjtMnphUqhpmqHm1Ay7q5W4LPrfrID9SA8BSyrnZH8AWVOv-QSMiKyKyCVTmIZOSP9VrogngdsAu9RQi9U3ilyNo8GZUF1_k6G'
                         . 'rYANYXnsljBalHGx1XN9vm6H3tRviXcVWuFvnyGUqIhIGofH6II-vN8eGKFC72pTg4zffiWD-jppjSGTPUYYqaqV53pmA5V9dFG'
                         . '_bpfj1gMHrd4V3DTKgx46WWxvNslAbQrQelSt5Ut107YUY7Wazhc31j9tc6iwc_dPhjuCQ5F19whLINb6hQBjtBCEYtjFsS4',
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'http://monitor.claropay.local.com'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'http://tareas.claropay.local.com'),
            ],
        ],

        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
                'rtind' => '050',
            ],
        ],

    ],

    'dev' => [

        // El ambiente cuenta con sandbox
        'sandbox' => false,
        // Servidores del ecosistema Claro Pagos
        'server' => [
            'admin' => [
                'url' => env('CP_ADMIN', 'http://atenea.dev.mavericksgateway.net'),
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'http://ares.dev.mavericksgateway.net'),
            ],
            'api' => [
                'url' => env('CP_API', 'ciclope.dev.mavericksgateway.net'),
            ],
            'boveda' => [
                'url' => env('CP_BOVEDA', 'http://busiris.dev.mavericksgateway.net'),
            ],
            'clientes' => [
                'url' => env('CP_CLIENTE', 'http://apolo.dev.mavericksgateway.net'),
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImZlMjg1M2Y5Y2UxOTgxY2Y0NWM4MGY0ODQ0MDU5M2EyMzJjYzg4ZmI'
                         . '3ZWIzYmViY2U2ODM2NjNiMWEyN2IyMzI3ZWI1YjVmOTBiYTBhYjdmIn0.eyJhdWQiOiIxIiwianRpIjoiZmUyODUzZjljZTE5OD'
                         . 'FjZjQ1YzgwZjQ4NDQwNTkzYTIzMmNjODhmYjdlYjNiZWJjZTY4MzY2M2IxYTI3YjIzMjdlYjViNWY5MGJhMGFiN2YiLCJpYXQiO'
                         . 'jE1MjA3NTE0ODcsIm5iZiI6MTUyMDc1MTQ4NywiZXhwIjoxNTUyMjg3NDg3LCJzdWIiOiIiLCJzY29wZXMiOlsic3VwZXJhZG1p'
                         . 'biJdfQ.bZ7RtLMT2WX9bPyGNM9GKgJE4Stwza1cYS1IjQGMN6K4cXZjyq1Rht0dOEcXlmUOne3anYxDziGvc81FY1ENf0M9nzpa'
                         . '6xS5B8t8YlZNawezAG6Ll7DkGW6GYXqe1_WC8Y1zAdp28-laTfJAiX5I8cm-oVXo4FhVhcex1u9OmUBvRor_chWlbdQE90GItbm'
                         . 'UHUOYAh0hajmG-tLQ_oMUBh0tiiDVH9CFylYNx3lSPZKnFNGdtUqoky1jTaiFwUvf4ZVsZ3Bww6ZoVsFR67LdCIUIzlxJ7TvWnW'
                         . 'sj8yDzwcbp2wF8guOeaE9QiwfoE8roWlVsigcL_3x3quilAtudLMiKSk9BW49T9qfZBGmHUXyBZTQUf2cUP2Fz2csMol62FhHYr'
                         . '0PjtMnphUqhpmqHm1Ay7q5W4LPrfrID9SA8BSyrnZH8AWVOv-QSMiKyKyCVTmIZOSP9VrogngdsAu9RQi9U3ilyNo8GZUF1_k6G'
                         . 'rYANYXnsljBalHGx1XN9vm6H3tRviXcVWuFvnyGUqIhIGofH6II-vN8eGKFC72pTg4zffiWD-jppjSGTPUYYqaqV53pmA5V9dFG'
                         . '_bpfj1gMHrd4V3DTKgx46WWxvNslAbQrQelSt5Ut107YUY7Wazhc31j9tc6iwc_dPhjuCQ5F19whLINb6hQBjtBCEYtjFsS4',
            ],
            'monitor' => [
                'url' => env('CP_MONITOR', 'http://medusa.dev.mavericksgateway.net'),
            ],
            'tareas' => [
                'url' => env('CP_TAREAS', 'http://triton.dev.mavericksgateway.net'),
            ],
        ],
        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
                'rtind' => '050',
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

        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
                'rtind' => '050',
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

        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
                'rtind' => '050',
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

        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
                'rtind' => '050',
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

        // Configuración de procesadores de pago
        'procesadores_pago' => [
            'amex' => [
                'api_url' => 'https://www206.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
                'rtind' => '035',
            ],
        ],

    ],

];
