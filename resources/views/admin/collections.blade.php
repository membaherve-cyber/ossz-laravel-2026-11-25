@extends('layouts.admin')

@section('title', t('nav.collections'))

@section('content')
    <div class="grid" style="grid-template-columns:1.3fr 1fr;align-items:start;gap:1.5rem">
        <div class="card" style="padding:1.5rem">
            <table class="table">
                <thead><tr><th>{{ is_fr() ? 'Collection' : 'Collection' }}</th><th>{{ is_fr() ? 'Saison' : 'Season' }}</th><th>{{ is_fr() ? 'Pièces' : 'Pieces' }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($collections as $collection)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:.75rem">
                                    @if ($collection->cover_image)
                                        <div style="width:40px;height:40px;border-radius:8px;overflow:hidden;background:var(--canvas);border:1px solid var(--line);flex-shrink:0">
                                            <img src="{{ $collection->cover_image }}" alt="" style="width:100%;height:100%;object-fit:cover">
                                        </div>
                                    @endif
                                    <div>
                                        <strong style="font-size:.85rem">{{ $collection->name }}</strong><br>
                                        <span style="font-size:.72rem;color:var(--muted)">{{ $collection->name_fr }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $collection->season }}</td>
                            <td>{{ $collection->products()->count() }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.collections.destroy', $collection) }}" onsubmit="return confirm('Delete this collection?')">
                                    @csrf
                                    <button class="btn btn-ghost btn-sm" type="submit" style="color:#b91c1c">✕</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="color:var(--muted)">{{ is_fr() ? 'Aucune collection.' : 'No collections.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card" style="padding:1.5rem">
            <h3 style="font-size:.95rem;font-weight:500;margin-bottom:1rem">+ {{ is_fr() ? 'Nouvelle collection' : 'New collection' }}</h3>
            <form method="POST" action="{{ route('admin.collections.store') }}" class="form-grid" enctype="multipart/form-data">
                @csrf
                <input class="field" name="name" placeholder="{{ is_fr() ? 'Nom (EN)' : 'Name (EN)' }}" required>
                <input class="field" name="name_fr" placeholder="{{ is_fr() ? 'Nom (FR)' : 'Name (FR)' }}">
                <input class="field" name="season" placeholder="{{ is_fr() ? 'Saison (ex. 2026)' : 'Season (e.g. 2026)' }}">
                
                {{-- Reusable ImageKit upload control --}}
                @include('admin.partials.image-upload', [
                    'name'     => 'cover_image',
                    'folder'   => 'collections',
                    'label'    => is_fr() ? 'Photo de couverture (ImageKit)' : 'Cover photo (ImageKit)',
                    'helpText' => 'Upload cover image directly to ImageKit or enter an image URL.'
                ])

                <textarea class="field" name="description" rows="2" placeholder="{{ is_fr() ? 'Description' : 'Description' }}"></textarea>
                <button class="btn btn-primary btn-sm" type="submit">{{ is_fr() ? 'Créer la collection' : 'Create collection' }}</button>
            </form>
        </div>
    </div>
@endsection
