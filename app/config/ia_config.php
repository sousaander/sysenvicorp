<?php
// app/config/ia_config.php

return [
    'python_api' => [
        'url' => 'http://localhost:8000',  // URL do serviço Python
        'timeout' => 10,
        'retries' => 3
    ],
    'sse' => [
        'url' => '/public_sse.php',        // Endpoint SSE
        'reconnect_timeout' => 5000        // ms
    ],
    'webhook' => [
        'token' => 'seu_token_super_secreto_aqui',
        'endpoint' => '/api_webhook.php'
    ],
    'sounds' => [
        'ping' => '/assets/sounds/ping.mp3',
        'chime' => '/assets/sounds/chime.mp3',
        'bell' => '/assets/sounds/bell.mp3'
    ],
    'portals' => [
        'pncp' => [
            'name' => 'PNCP Nacional',
            'enabled' => true,
            'crawler_class' => 'PncpCrawler'
        ],
        'comprasnet' => [
            'name' => 'ComprasNet / SIASG',
            'enabled' => true,
            'crawler_class' => 'ComprasNetCrawler'
        ],
        'bec' => [
            'name' => 'BEC São Paulo',
            'enabled' => true,
            'crawler_class' => 'BecSPCrawler'
        ],
        'bll' => [
            'name' => 'BLL Integrado',
            'enabled' => true,
            'crawler_class' => 'BllCrawler'
        ]
    ]
];  