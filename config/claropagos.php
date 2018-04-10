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
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImMxNjhhZjQ0ZDk0YmVjMjNmMDA4ZWI4YmI0NGIwODY2ZDE1NWZkNTc'
                         . '3MTRlN2M3MWQ2NTkzZGQ5OGM2N2U1MGZkMGZjYzVjYWYxNDBlN2RjIn0.eyJhdWQiOiIxIiwianRpIjoiYzE2OGFmNDRkOTRiZW'
                         . 'MyM2YwMDhlYjhiYjQ0YjA4NjZkMTU1ZmQ1NzcxNGU3YzcxZDY1OTNkZDk4YzY3ZTUwZmQwZmNjNWNhZjE0MGU3ZGMiLCJpYXQiO'
                         . 'jE1MjEwNjMxNDAsIm5iZiI6MTUyMTA2MzE0MCwiZXhwIjoxNTUyNTk5MTQwLCJzdWIiOiIiLCJzY29wZXMiOlsic3VwZXJhZG1p'
                         . 'biJdfQ.UAedk3NG3v4XnDD-4-4MuFTgC8KoF_w97oBBs1_RRx2j1uWIvDn3zjp2RblPnaVU1hs_LfszrwlUU3PrB43kdPvtkXCL'
                         . '2dZ_RSot4O-V9O7tMVLENofCuXVcx5pZIbKi0xWmOkYATRcM2egMNM1QSjqRqm4Kn7eOEInWxs3N9QiLl0hRcqQZa7q-JRvVbx0'
                         . 'ThtUc0rRgMCTxUXS3Ezu9i4KlKTE1q2SYAhnu_bd7pWnqvoXAc2W3v_GB8CujnnpimrwXxG6-bPvBABAykxeXYQIPeiRo9wYqs8'
                         . 'XDELcsup_Z6w2_FWFi5cacVu481059_4QCA-BfHoolhF7hniV8Lf7Ezaj-LZIwDBMGOt8MWDI93KwauN8IfBYYPj2daQwsxpiho'
                         . 'psUl1tO55ZGY69vLSXtwc38qfwJQgca4VMtbu-WnIStnsbLAmIfTAge8kLtDY9ybe4azQqRk1DsLU70OAWyx-Venlx9ado2bqgC'
                         . 'n9J97jSHDDa68BTUYcWa4TmJdbf-1ihcroKrGKwpANTFqphMK15J0iyoyd7KS0stM4dDS4cu8V4dtcHMgHedGvlXnGNEVdyD0jf'
                         . 'YZlgFhaT5wdqVVfnZH1AdMZ05V40YAUWhiW1L90RrJEMBsj36LNN4BO-uuqS5bZSMx07Yww2hADLNFGAE6yst0t3pNllhKAE',
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'http://antifraude.claropay.local.com'),
            ],
            'api' => [
                'url' => env('CP_API', 'http://api.claropay.local.com'),
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ'
                         . '0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZW'
                         . 'YzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiO'
                         . 'jE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRt'
                         . 'aW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3'
                         . 'AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0'
                         . 'CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyA'
                         . 'LyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW'
                         . '6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLR'
                         . 'Yk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_8'
                         . '0XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc',
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
                // Producción
//                'api_url' => 'https://www206.americanexpress.com/IPPayments/inter/CardAuthorization.do',
//                'rtind' => '035',
                // Sandbox
                'api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                'rtind' => '050',
                // Común
                'origin' => 'AMERICAMOVIL-28705',
                'country' => '484',
                'region' => 'LAC',
            ],
            'eglobal' => [
                // Proxy
                'proxy' => [
                    'ip' => '127.0.0.1',
                    'puerto' => '8300',
                    'timeout' => 60,
                    'verbose' => 'info', // emergency, alert, critical, error, warning, notice, info and debug
                ],
                // Servidor
                'ip' => '127.0.0.1',
                'puerto' => '8315',
                'timeout' => 120,
                'keepalive' => 30,
                // Afiliación
                'afiliacion' => '5462742',
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
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImMxNjhhZjQ0ZDk0YmVjMjNmMDA4ZWI4YmI0NGIwODY2ZDE1NWZkNTc'
                         . '3MTRlN2M3MWQ2NTkzZGQ5OGM2N2U1MGZkMGZjYzVjYWYxNDBlN2RjIn0.eyJhdWQiOiIxIiwianRpIjoiYzE2OGFmNDRkOTRiZW'
                         . 'MyM2YwMDhlYjhiYjQ0YjA4NjZkMTU1ZmQ1NzcxNGU3YzcxZDY1OTNkZDk4YzY3ZTUwZmQwZmNjNWNhZjE0MGU3ZGMiLCJpYXQiO'
                         . 'jE1MjEwNjMxNDAsIm5iZiI6MTUyMTA2MzE0MCwiZXhwIjoxNTUyNTk5MTQwLCJzdWIiOiIiLCJzY29wZXMiOlsic3VwZXJhZG1p'
                         . 'biJdfQ.UAedk3NG3v4XnDD-4-4MuFTgC8KoF_w97oBBs1_RRx2j1uWIvDn3zjp2RblPnaVU1hs_LfszrwlUU3PrB43kdPvtkXCL'
                         . '2dZ_RSot4O-V9O7tMVLENofCuXVcx5pZIbKi0xWmOkYATRcM2egMNM1QSjqRqm4Kn7eOEInWxs3N9QiLl0hRcqQZa7q-JRvVbx0'
                         . 'ThtUc0rRgMCTxUXS3Ezu9i4KlKTE1q2SYAhnu_bd7pWnqvoXAc2W3v_GB8CujnnpimrwXxG6-bPvBABAykxeXYQIPeiRo9wYqs8'
                         . 'XDELcsup_Z6w2_FWFi5cacVu481059_4QCA-BfHoolhF7hniV8Lf7Ezaj-LZIwDBMGOt8MWDI93KwauN8IfBYYPj2daQwsxpiho'
                         . 'psUl1tO55ZGY69vLSXtwc38qfwJQgca4VMtbu-WnIStnsbLAmIfTAge8kLtDY9ybe4azQqRk1DsLU70OAWyx-Venlx9ado2bqgC'
                         . 'n9J97jSHDDa68BTUYcWa4TmJdbf-1ihcroKrGKwpANTFqphMK15J0iyoyd7KS0stM4dDS4cu8V4dtcHMgHedGvlXnGNEVdyD0jf'
                         . 'YZlgFhaT5wdqVVfnZH1AdMZ05V40YAUWhiW1L90RrJEMBsj36LNN4BO-uuqS5bZSMx07Yww2hADLNFGAE6yst0t3pNllhKAE',
            ],
            'antifraude' => [
                'url' => env('CP_ANTIFRAUDE', 'http://ares.dev.mavericksgateway.net'),
            ],
            'api' => [
                'url' => env('CP_API', 'ciclope.dev.mavericksgateway.net'),
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ'
                         . '0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZW'
                         . 'YzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiO'
                         . 'jE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRt'
                         . 'aW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3'
                         . 'AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0'
                         . 'CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyA'
                         . 'LyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW'
                         . '6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLR'
                         . 'Yk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_8'
                         . '0XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc',
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
            'eglobal' => [
                // Proxy
                'proxy' => [
                    'ip' => '127.0.0.1',
                    'puerto' => '8300',
                    'timeout' => 120,
                    'verbose' => 'error',
                ],
                // Servidor
                'ip' => '172.26.202.4',
                'puerto' => '8315',
                'timeout' => 120,
                'keepalive' => 30,
                // Afiliación
                'afiliacion' => '5462742',
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
            'eglobal' => [
                // Proxy
                'proxy' => [
                    'ip' => '127.0.0.1',
                    'puerto' => '8300',
                    'timeout' => 15,
                    'verbose' => 'error',
                ],
                // Servidor
                'ip' => '127.0.0.1',
                'puerto' => '8300',
                'timeout' => 60,
                'keepalive' => 40,
                // Afiliación
                'afiliacion' => '5462742',
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
            'eglobal' => [
                // Proxy
                'proxy' => [
                    'ip' => '127.0.0.1',
                    'puerto' => '8300',
                    'timeout' => 15,
                    'verbose' => 'warning',
                ],
                // Servidor
                'ip' => '127.0.0.1',
                'puerto' => '8300',
                'timeout' => 60,
                'keepalive' => 30,
                // Afiliación
                'afiliacion' => '5462742',
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
            'eglobal' => [
                // Proxy
                'proxy' => [
                    'ip' => '127.0.0.1',
                    'puerto' => '8300',
                    'timeout' => 15,
                    'verbose' => 'warning',
                ],
                // Servidor
                'ip' => '172.26.202.4',
                'puerto' => '8315',
                'timeout' => 60,
                'keepalive' => 30,
                // Afiliación
                'afiliacion' => '5462742',
            ],
        ],

    ],

];
