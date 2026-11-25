<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\ImageKit;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        $this->guard();

        return view('admin.media', [
            'media' => Media::orderByDesc('created_at')->limit(200)->get(),
        ]);
    }

    public function upload(Request $request)
    {
        $this->guard();
        $request->validate([
            // Explicit allowlist (never accept arbitrary files; svg excluded
            // because browsers execute scripts inside inline SVG).
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,avif,gif,mp4,webm,mov,pdf|max:51200',
            'tags' => 'nullable|string|max:200',
        ]);
        $file = $request->file('file');
        $result = ImageKit::upload($file, 'media');
        $url = $result['url'];
        Media::create([
            'url'         => $url,
            'alt_text'    => (string) $request->input('alt', ''),
            'tags'        => (string) $request->input('tags', ''),
            'uploaded_by' => request()->attributes->get('ossz_user')->id,
        ]);

        return back()->with('success', 'File uploaded: ' . $url);
    }

    /**
     * JSON API endpoint for asynchronous ImageKit upload controls across the backoffice.
     */
    public function apiUpload(Request $request)
    {
        $this->guard();

        try {
            $request->validate([
                'file'   => 'required|file|mimes:jpg,jpeg,png,webp,avif,gif|max:15360',
                'folder' => 'nullable|string|in:products,lookbook,collections,journal,homepage-blocks,media',
            ]);

            $folder = (string) $request->input('folder', 'products');
            $file = $request->file('file');

            $result = ImageKit::upload($file, $folder);

            return response()->json([
                'ok'      => true,
                'url'     => $result['url'],
                'fileId'  => $result['fileId'] ?? null,
                'name'    => $result['name'] ?? '',
                'storage' => $result['storage'] ?? 'imagekit',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'ok'    => false,
                'error' => collect($ve->errors())->flatten()->first() ?: 'Validation failed.',
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage() ?: 'Image upload failed.',
            ], 500);
        }
    }

    private function guard(): void
    {
        $user = request()->attributes->get('ossz_user');
        if (! \App\Support\Auth::canManageCatalogue($user->role ?? 'customer')) {
            abort(redirect()->route('admin.dashboard')->with('error', 'Insufficient permissions.'));
        }
    }
}
