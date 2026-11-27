<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── mbstring safety shim ────────────────────────────────────────────────
// Namecheap's CloudLinux PHP selector can boot a PHP build without the
// mbstring extension (observed 2026-11-27: selector change silently
// dropped mbstring). symfony/polyfill-mbstring covers every mb_* function
// Laravel needs EXCEPT mb_split(), which Str::studly() calls on every
// request (session driver resolution) — without it the whole site 500s.
// This shim restores the one missing function; the real extension, when
// present, always wins (shim only registers if mb_split is undefined).
if (!function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        // mb_split() takes an mb-regex pattern; PCRE with /u is a faithful
        // stand-in for the ASCII-family patterns Laravel uses. The '~'
        // delimiter is escaped inside the pattern to keep it inert.
        return preg_split('~'.str_replace('~', '\\~', $pattern).'~u', $string, $limit);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
