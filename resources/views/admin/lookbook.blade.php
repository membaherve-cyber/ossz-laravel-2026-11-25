@extends('layouts.admin')

@section('title', t('nav.lookbook'))

@section('content')
    <div style="display:grid;gap:1.5rem">
        {{-- Existing looks list --}}
        @foreach ($looks as $look)
            <form method="POST" action="{{ route('admin.lookbook.save') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $look->id }}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <span class="pill {{ $look->media_type === 'video' ? 'pill-blue' : 'pill-gray' }}">
                            {{ strtoupper($look->media_type) }}
                        </span>
                        <strong style="font-size:.9rem">{{ $look->title ?: 'Untitled Look' }}</strong>
                    </div>
                    <button type="submit" form="del-look-{{ $look->id }}" class="btn btn-ghost btn-sm" style="color:#b91c1c" onclick="return confirm('Delete this look?')">✕ {{ is_fr() ? 'Supprimer' : 'Delete' }}</button>
                </div>

                <div class="form-grid cols-2">
                    <input class="field" name="title" value="{{ $look->title }}" placeholder="Title">
                    <select class="field" name="media_type">
                        <option value="image" @selected($look->media_type === 'image')>Image</option>
                        <option value="video" @selected($look->media_type === 'video')>Video</option>
                    </select>
                </div>

                <div style="margin-top:1rem">
                    @include('admin.partials.image-upload', [
                        'name'     => 'image_url',
                        'value'    => $look->image_url,
                        'folder'   => 'lookbook',
                        'label'    => is_fr() ? 'Image du look (ImageKit)' : 'Look imagery (ImageKit)',
                        'helpText' => 'Upload look image directly to ImageKit or paste an external URL.'
                    ])
                </div>

                <div class="form-grid cols-2" style="margin-top:1rem">
                    <input class="field" name="video_url" value="{{ $look->video_url }}" placeholder="Video URL (mp4 / vimeo / youtube)">
                    <input class="field" name="poster_url" value="{{ $look->poster_url }}" placeholder="Video Poster URL (optional)">
                    <input class="field" name="product_slug" value="{{ $look->product_slug }}" placeholder="Product slug (e.g. midnight-agbada)">
                    <input class="field" type="number" name="sort_order" value="{{ $look->sort_order }}" placeholder="Sort order">
                </div>

                <div class="form-grid cols-2" style="margin-top:.8rem">
                    <input class="field" name="caption" value="{{ $look->caption }}" placeholder="Caption (EN)">
                    <input class="field" name="caption_fr" value="{{ $look->caption_fr }}" placeholder="Caption (FR)">
                </div>

                <div style="margin-top:1rem">
                    <button class="btn btn-primary btn-sm" type="submit">{{ is_fr() ? 'Enregistrer les modifications' : 'Save changes' }}</button>
                </div>
            </form>

            <form id="del-look-{{ $look->id }}" method="POST" action="{{ route('admin.lookbook.delete') }}" style="display:none">
                @csrf
                <input type="hidden" name="id" value="{{ $look->id }}">
            </form>
        @endforeach

        {{-- Creation forms: New look + Video upload --}}
        <div class="grid" style="grid-template-columns:1.2fr 1fr;gap:1.5rem">
            <form method="POST" action="{{ route('admin.lookbook.save') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
                @csrf
                <h3 style="font-size:.95rem;font-weight:500;margin-bottom:1rem">+ {{ is_fr() ? 'Nouveau look' : 'New look' }}</h3>
                <div class="form-grid">
                    <input class="field" name="title" placeholder="Title">

                    @include('admin.partials.image-upload', [
                        'name'     => 'image_url',
                        'folder'   => 'lookbook',
                        'label'    => is_fr() ? 'Photo du look (ImageKit)' : 'Look photo (ImageKit)',
                        'helpText' => 'Upload image from computer directly to ImageKit.'
                    ])

                    <input class="field" name="caption" placeholder="Caption">
                    <input class="field" name="product_slug" placeholder="Tagged product-slug (e.g. midnight-agbada)">
                    <button class="btn btn-primary btn-sm" type="submit">{{ is_fr() ? 'Créer le look' : 'Create look' }}</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.lookbook.upload') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
                @csrf
                <h3 style="font-size:.95rem;font-weight:500;margin-bottom:.5rem">{{ is_fr() ? 'Téléverser une vidéo' : 'Upload lookbook video' }}</h3>
                <p style="font-size:.78rem;color:var(--muted);margin-bottom:1rem">MP4, WebM or MOV up to 100 MB. Execution guard 120s.</p>
                <div class="form-grid">
                    <input class="field" type="file" name="video" accept="video/mp4,video/webm,video/quicktime" required>
                    <button class="btn btn-secondary btn-sm" type="submit">{{ is_fr() ? 'Téléverser la vidéo' : 'Upload video' }}</button>
                </div>
                @if (session('uploaded_path'))
                    <div class="card status-badge success" style="margin-top:1rem;padding:.6rem .8rem;font-size:.75rem">
                        ✓ Stored at: <code>{{ session('uploaded_path') }}</code>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
