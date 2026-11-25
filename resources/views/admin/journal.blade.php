@extends('layouts.admin')

@section('title', t('nav.journal'))

@section('content')
    <div style="display:grid;gap:1.5rem">
        {{-- Existing entries list --}}
        @foreach ($posts as $post)
            <form method="POST" action="{{ route('admin.journal.save') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $post->id }}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <span class="pill {{ $post->status === 'published' ? 'pill-green' : 'pill-gray' }}">
                            {{ strtoupper($post->status) }}
                        </span>
                        <strong style="font-size:.9rem">{{ $post->title }}</strong>
                    </div>
                    <button type="submit" form="del-post-{{ $post->id }}" class="btn btn-ghost btn-sm" style="color:#b91c1c" onclick="return confirm('Delete this journal post?')">✕ {{ is_fr() ? 'Supprimer' : 'Delete' }}</button>
                </div>

                <div class="form-grid cols-2">
                    <input class="field" name="title" value="{{ $post->title }}" placeholder="Title (EN)" required>
                    <input class="field" name="title_fr" value="{{ $post->title_fr }}" placeholder="Title (FR)">
                    <input class="field" name="slug" value="{{ $post->slug }}" placeholder="slug">
                    <input class="field" name="author_name" value="{{ $post->author_name }}" placeholder="Author name">
                    <select class="field" name="status">
                        <option value="draft" @selected($post->status === 'draft')>{{ is_fr() ? 'Brouillon' : 'Draft' }}</option>
                        <option value="published" @selected($post->status === 'published')>{{ is_fr() ? 'Publié' : 'Published' }}</option>
                    </select>
                </div>

                <div style="margin-top:1rem">
                    @include('admin.partials.image-upload', [
                        'name'     => 'cover_image',
                        'value'    => $post->cover_image,
                        'folder'   => 'journal',
                        'label'    => is_fr() ? 'Image de couverture éditoriale (ImageKit)' : 'Editorial cover image (ImageKit)',
                        'helpText' => 'Upload image from computer directly to ImageKit or enter an image link.'
                    ])
                </div>

                <div class="form-grid cols-2" style="margin-top:.8rem">
                    <textarea class="field" name="excerpt" rows="2" placeholder="Excerpt EN">{{ $post->excerpt }}</textarea>
                    <textarea class="field" name="excerpt_fr" rows="2" placeholder="Excerpt FR">{{ $post->excerpt_fr }}</textarea>
                    <textarea class="field" name="body" rows="6" placeholder="Body EN">{{ $post->body }}</textarea>
                    <textarea class="field" name="body_fr" rows="6" placeholder="Body FR">{{ $post->body_fr }}</textarea>
                </div>

                <div style="margin-top:1rem">
                    <button class="btn btn-primary btn-sm" type="submit">{{ is_fr() ? 'Enregistrer les modifications' : 'Save changes' }}</button>
                </div>
            </form>

            <form id="del-post-{{ $post->id }}" method="POST" action="{{ route('admin.journal.delete') }}" style="display:none">
                @csrf
                <input type="hidden" name="id" value="{{ $post->id }}">
            </form>
        @endforeach

        {{-- Creation form --}}
        <form method="POST" action="{{ route('admin.journal.save') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
            @csrf
            <h3 style="font-size:.95rem;font-weight:500;margin-bottom:1rem">+ {{ is_fr() ? 'Nouvel article' : 'New journal entry' }}</h3>
            <div class="form-grid cols-2">
                <input class="field" name="title" placeholder="Title EN" required>
                <input class="field" name="title_fr" placeholder="Title FR">
                <select class="field" name="status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>

            <div style="margin-top:1rem">
                @include('admin.partials.image-upload', [
                    'name'     => 'cover_image',
                    'folder'   => 'journal',
                    'label'    => is_fr() ? 'Image de couverture éditoriale (ImageKit)' : 'Editorial cover image (ImageKit)',
                    'helpText' => 'Select an image from computer to automatically upload to ImageKit.'
                ])
            </div>

            <textarea class="field" name="excerpt" rows="2" placeholder="Excerpt summary" style="margin-top:.8rem"></textarea>
            <textarea class="field" name="body" rows="6" placeholder="Full story text..." style="margin-top:.8rem"></textarea>
            <div style="margin-top:1rem">
                <button class="btn btn-primary btn-sm" type="submit">{{ is_fr() ? 'Créer l\'article' : 'Create entry' }}</button>
            </div>
        </form>
    </div>
@endsection
