<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageKit
{
    /**
     * Upload an image to ImageKit via their REST API, or fall back cleanly to local public storage.
     *
     * @param UploadedFile $file
     * @param string $folder One of 'products', 'lookbook', 'collections', 'journal', 'homepage-blocks'
     * @return array{ok: bool, url: string, fileId?: string, storage: string, name: string}
     */
    public static function upload(UploadedFile $file, string $folder = 'products'): array
    {
        $privateKey  = trim((string) env('IMAGEKIT_PRIVATE_KEY'));
        $originalName = $file->getClientOriginalName();
        $cleanName    = preg_replace('/[^a-zA-Z0-9._-]/', '-', $originalName);
        $uniqueName   = time() . '-' . $cleanName;

        if ($privateKey !== '') {
            try {
                $response = Http::withBasicAuth($privateKey, '')
                    ->timeout(30)
                    ->attach('file', file_get_contents($file->getRealPath()), $originalName)
                    ->post('https://upload.imagekit.io/api/v1/files/upload', [
                        'fileName'          => $uniqueName,
                        'folder'            => "/ossz/{$folder}/",
                        'useUniqueFileName' => 'true',
                        'tags'              => "ossz,{$folder}",
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    return [
                        'ok'      => true,
                        'url'     => $json['url'] ?? '',
                        'fileId'  => $json['fileId'] ?? null,
                        'name'    => $json['name'] ?? $uniqueName,
                        'storage' => 'imagekit',
                    ];
                }

                Log::warning('ImageKit upload non-200 response: ' . $response->body());
            } catch (\Throwable $e) {
                Log::error('ImageKit upload exception: ' . $e->getMessage());
            }
        }

        // Graceful local public storage fallback
        $path = $file->store("uploads/{$folder}", 'public');
        return [
            'ok'      => true,
            'url'     => asset("storage/{$path}"),
            'name'    => $originalName,
            'storage' => 'local',
        ];
    }
}
