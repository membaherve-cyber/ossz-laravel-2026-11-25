@extends('layouts.admin')

@section('title', is_fr() ? 'Blocs d\'accueil' : 'Homepage blocks')

@section('content')
    <div style="display:grid;gap:1.5rem">
        {{-- Existing blocks list --}}
        @foreach ($blocks as $block)
            <form method="POST" action="{{ route('admin.homepage.save') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $block->id }}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <span class="pill pill-blue">{{ strtoupper($block->type) }}</span>
                        <strong style="font-size:.9rem">{{ $block->heading ?: 'Untitled block' }}</strong>
                    </div>
                    <div style="display:flex;gap:1rem;align-items:center">
                        <label style="font-size:.8rem;display:flex;gap:.4rem;align-items:center;cursor:pointer">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1" @checked($block->is_published)>
                            {{ is_fr() ? 'Publié' : 'Published' }}
                        </label>
                        <button type="submit" form="del-block-{{ $block->id }}" class="btn btn-ghost btn-sm" style="color:#b91c1c" onclick="return confirm('Delete this block?')">✕ {{ is_fr() ? 'Supprimer' : 'Delete' }}</button>
                    </div>
                </div>

                <div class="form-grid cols-2">
                    <select class="field" name="type">
                        <option value="hero" @selected($block->type === 'hero')>Hero (Banner principal)</option>
                        <option value="banner" @selected($block->type === 'banner')>Banner (Plein écran)</option>
                        <option value="quote" @selected($block->type === 'quote')>Quote (Citation)</option>
                        <option value="editorial" @selected($block->type === 'editorial')>Editorial (Atelier)</option>
                    </select>
                    <input class="field" name="cta_href" value="{{ $block->cta_href }}" placeholder="CTA URL (e.g. /shop)">
                    <input class="field" name="eyebrow" value="{{ $block->eyebrow }}" placeholder="Eyebrow EN">
                    <input class="field" name="eyebrow_fr" value="{{ $block->eyebrow_fr }}" placeholder="Eyebrow FR">
                    <input class="field" name="heading" value="{{ $block->heading }}" placeholder="Heading EN">
                    <input class="field" name="heading_fr" value="{{ $block->heading_fr }}" placeholder="Heading FR">
                    <input class="field" name="cta_label" value="{{ $block->cta_label }}" placeholder="CTA EN">
                    <input class="field" name="cta_label_fr" value="{{ $block->cta_label_fr }}" placeholder="CTA FR">
                </div>

                <div style="margin-top:1rem">
                    @include('admin.partials.image-upload', [
                        'name'     => 'image_url',
                        'value'    => $block->image_url,
                        'folder'   => 'homepage-blocks',
                        'label'    => is_fr() ? 'Visuel du bloc (ImageKit)' : 'Block imagery (ImageKit)',
                        'helpText' => 'Upload image from computer directly to ImageKit or enter an image link.'
                    ])
                </div>

                <div class="form-grid cols-2" style="margin-top:.8rem">
                    <textarea class="field" name="body" rows="2" placeholder="Body EN">{{ $block->body }}</textarea>
                    <textarea class="field" name="body_fr" rows="2" placeholder="Body FR">{{ $block->body_fr }}</textarea>
                    <input class="field" type="number" name="sort_order" value="{{ $block->sort_order }}" placeholder="Sort order">
                </div>

                <div style="margin-top:1rem">
                    <button class="btn btn-primary btn-sm" type="submit">{{ is_fr() ? 'Enregistrer les modifications' : 'Save changes' }}</button>
                </div>
            </form>

            <form id="del-block-{{ $block->id }}" method="POST" action="{{ route('admin.homepage.delete') }}" style="display:none">
                @csrf
                <input type="hidden" name="id" value="{{ $block->id }}">
            </form>
        @endforeach

        {{-- Creation form --}}
        <form method="POST" action="{{ route('admin.homepage.save') }}" class="card" style="padding:1.5rem" enctype="multipart/form-data">
            @csrf
            <h3 style="font-size:.95rem;font-weight:500;margin-bottom:1rem">+ {{ is_fr() ? 'Nouveau bloc d\'accueil' : 'New homepage block' }}</h3>
            <div class="form-grid cols-2">
                <select class="field" name="type">
                    <option value="hero">Hero (Banner principal)</option>
                    <option value="banner">Banner (Plein écran)</option>
                    <option value="quote">Quote (Citation)</option>
                    <option value="editorial">Editorial (Atelier)</option>
                </select>
                <input class="field" name="cta_href" placeholder="CTA link (e.g. /shop)">
                <input class="field" name="heading" placeholder="Heading EN" required>
                <input class="field" name="heading_fr" placeholder="Heading FR">
                <input class="field" name="cta_label" placeholder="CTA button label">
            </div>

            <div style="margin-top:1rem">
                @include('admin.partials.image-upload', [
                    'name'     => 'image_url',
                    'folder'   => 'homepage-blocks',
                    'label'    => is_fr() ? 'Visuel du bloc (ImageKit)' : 'Block imagery (ImageKit)',
                    'helpText' => 'Select an image from computer to automatically upload to ImageKit.'
                ])
            </div>

            <button class="btn btn-primary btn-sm" style="margin-top:1rem" type="submit">{{ is_fr() ? 'Créer le bloc' : 'Create block' }}</button>
        </form>
    </div>
@endsection
