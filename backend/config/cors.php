<?php

return [
    'paths' => ['api/*', 'dashboard/*', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://grupify.cat', 'https://www.grupify.cat', 'http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
