<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => 'sessions',
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'ossz_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    // Secure cookies on HTTPS hosts (production sets SESSION_SECURE_COOKIE=true;
    // the local sandbox stays unset so plain-HTTP sessions still work).
    // NOTE: App\Http\Middleware\SchemeAwareCookies overrides secure /
    // same_site / partitioned at request time — HTTPS serves
    // SameSite=None; Secure; Partitioned, HTTP serves Lax non-Secure.
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
    // Escape hatch mirroring the Next.js COOKIE_CROSS_SITE=0 switch:
    // when truthy, cookies stay SameSite=Lax / non-Secure regardless of
    // the request scheme.
    'cross_site_disabled' => env('COOKIE_CROSS_SITE') === '0',
];
