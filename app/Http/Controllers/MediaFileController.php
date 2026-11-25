<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves files uploaded to storage/app (payment proofs, lookbook videos,
 * media library) through a controller so shared hosts never need a
 * storage symlink.
 */
class MediaFileController extends Controller
{
    public function show(string $path)
    {
        $base = realpath(storage_path('app'));
        $full = realpath(storage_path('app'.DIRECTORY_SEPARATOR.$path));

        // Guard against traversal and sibling directories (storage-app-x etc.).
        if ($base === false || $full === false || ! str_starts_with($full, $base.DIRECTORY_SEPARATOR) || ! is_file($full)) {
            abort(404);
        }

        $mime = mime_content_type($full) ?: 'application/octet-stream';
        $inlineSafe = str_starts_with($mime, 'image/')
            && ! str_contains($mime, 'svg') // SVG can carry scripts — force download.
            && ! str_contains($mime, 'image/x-icon');

        return new BinaryFileResponse($full, 200, array_filter([
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
            // Anything that is not a plain raster image downloads instead of
            // rendering on our origin (blocks stored XSS via uploads).
            'Content-Disposition' => $inlineSafe ? null : 'attachment; filename="'.basename($full).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]));
    }
}
