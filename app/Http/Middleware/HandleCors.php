<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\HandleCors as Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleCors extends Middleware
{
    protected function defaults(): array
    {
        return [
            'paths' => ['api/*', 'sanctum/csrf-cookie'],
            'allowed_methods' => ['*'],
            'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],
            'allowed_origins_patterns' => [],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 0,
            'supports_credentials' => true,
        ];
    }
}
