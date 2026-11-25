@extends('layouts.app')

@section('title', t('shop.title'))

@section('content')
@php
    $loc    = locale();
    $q      = request()->query();
    $catAct = $q['category'] ?? '';
    $colAct = $q['collection'] ?? '';
    $szeAct = $q['size'] ?? '';
    $clrAct = $q['colour'] ?? '';
    $avlAct = $q['availability'] ?? '';
    $srtAct = $q['sort'] ?? 'newest';
    $minAct = $q['min'] ?? '';
    $maxAct = $q['max'] ?? '';
    $pageNo = $pageNo ?? max(1, (int)($q['page'] ?? 1));
    $totalPages = $totalPages ?? 1;
    $view   = ($q['view'] ?? '') === 'list' ? 'list' : 'grid';

    // Helper: build href for a chip toggle
    function chipHref(string $key, string $value, array $q): string {
        $p = $q;
        if (($p[$key] ?? '') === $value) {
            unset($p[$key]);
        } else {
            $p[$key] = $value;
        }
        unset($p['page']);
        $qs = http_build_query($p);
        return '/shop' . ($qs ? '?'.$qs : '');
    }

    $activeCount = collect(['category','collection','size','colour','availability','min','max'])->filter(fn($k) => !empty($q[$k]))->count();

    $priceBands = [
        [t('shop.under'),  '',       '80000'],
        [t('shop.band2'),  '80000',  '150000'],
        [t('shop.band3'),  '150000', '250000'],
        [t('shop.above'),  '250000', ''],
    ];
    $sortKeys = [
        ['newest',     t('shop.newest')],
        ['price_asc',  t('shop.priceAsc')],
        ['price_desc', t('shop.priceDesc')],
        ['popular',    t('shop.popular')],
    ];
@endphp

<div class="wrap" style="padding:3rem 0 1rem">
    <p class="eyebrow">{{ t('shop.eyebrow') }}</p>
    <h1 class="display" style="margin-top:.5rem;font-size:2.5rem;">{{ t('shop.title') }}</h1>
</div>

{{-- Service strip --}}
<div class="wrap">
    <div style="display:grid;gap:1.5rem;border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:1.5rem 0;text-align:center;grid-template-columns:repeat(2,1fr);">
        @foreach([
            [t('home.svc1'), t('home.svc1b')],
            [t('home.svc2'), t('home.svc2b')],
            [t('home.svc3'), t('home.svc3b')],
            [t('home.svc4'), t('home.svc4b')],
        ] as [$sTitle, $sSub])
        <div>
            <p style="font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;">{{ $sTitle }}</p>
            <p style="margin-top:.25rem;font-size:.72rem;color:var(--muted);">{{ $sSub }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Search bar --}}
<div class="wrap" style="margin-top:2rem;max-width:640px;">
    <form method="GET" action="{{ route('shop') }}" style="position:relative;">
        @foreach($q as $k => $v)
            @if($k !== 'q' && $k !== 'page')
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
        <div class="glossy-search" style="display:flex;align-items:center;gap:.5rem;padding:.6rem .9rem;position:relative;overflow:hidden;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="search" name="q" value="{{ $query }}"
                   placeholder="{{ t('search.placeholder') }}"
                   style="flex:1;border:none;background:transparent;outline:none;font-size:.88rem;font-family:inherit;color:var(--ink);">
            @if($query)
                <a href="{{ route('shop') }}" style="font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);">{{ t('shop.searchClear') }}</a>
            @endif
            <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink:0;">{{ t('search.cta') }}</button>
        </div>
    </form>
</div>

{{-- Main layout: sticky filter sidebar + product area --}}
<div class="wrap" style="margin-top:2.5rem;padding-bottom:5rem;">
    <div id="shop-layout" style="display:grid;gap:2.5rem;grid-template-columns:1fr;">

        {{-- Filter sidebar --}}
        <aside id="filter-sidebar" style="display:none;flex-direction:column;gap:2rem;">

            {{-- Header: Filters + count + clear --}}
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p class="eyebrow">{{ t('shop.filters') }}{{ $activeCount ? ' ('.$activeCount.')' : '' }}</p>
                @if($activeCount)
                    <a href="{{ route('shop') }}" style="font-size:.75rem;color:var(--accent);" class="link-underline">{{ t('shop.clearAll') }}</a>
                @endif
            </div>

            {{-- Category --}}
            @if(count($facets['categories']))
            <div>
                <p style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-weight:500;">{{ t('shop.category') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    @foreach($facets['categories'] as $cat)
                        <a href="{{ chipHref('category', $cat->slug, $q) }}"
                           class="chip{{ $catAct === $cat->slug ? ' chip-active' : '' }}">
                            {{ \App\Support\I18n::pick($cat->name, $cat->name_fr) }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Collection --}}
            @if(count($facets['collections']))
            <div>
                <p style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-weight:500;">{{ t('shop.collection') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    @foreach($facets['collections'] as $col)
                        <a href="{{ chipHref('collection', $col->slug, $q) }}"
                           class="chip{{ $colAct === $col->slug ? ' chip-active' : '' }}">
                            {{ \App\Support\I18n::pick($col->name, $col->name_fr) }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Size --}}
            @if(count($facets['sizes']))
            <div>
                <p style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-weight:500;">{{ t('buy.size') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    @foreach($facets['sizes'] as $sz)
                        <a href="{{ chipHref('size', $sz, $q) }}"
                           class="chip{{ $szeAct === $sz ? ' chip-active' : '' }}">{{ $sz }}</a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Colour --}}
            @if(count($facets['colours']))
            <div>
                <p style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-weight:500;">{{ t('shop.colour') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    @foreach($facets['colours'] as $cl)
                        <a href="{{ chipHref('colour', $cl, $q) }}"
                           class="chip{{ $clrAct === $cl ? ' chip-active' : '' }}">{{ $cl }}</a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Price bands --}}
            <div>
                <p style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-weight:500;">{{ t('shop.priceRange') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    @foreach($priceBands as [$label, $min, $max])
                    @php
                        $isActiveBand = ($minAct === $min && $maxAct === $max);
                        $bandP = $q;
                        if ($isActiveBand) { unset($bandP['min']); unset($bandP['max']); }
                        else { if($min) $bandP['min'] = $min; else unset($bandP['min']); if($max) $bandP['max'] = $max; else unset($bandP['max']); }
                        unset($bandP['page']);
                        $bandHref = '/shop' . (count($bandP) ? '?'.http_build_query($bandP) : '');
                    @endphp
                        <a href="{{ $bandHref }}" class="chip{{ $isActiveBand ? ' chip-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Availability --}}
            <div>
                <p style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.75rem;font-weight:500;">{{ t('shop.availability') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    <a href="{{ chipHref('availability', 'in_stock', $q) }}"
                       class="chip{{ $avlAct === 'in_stock' ? ' chip-active' : '' }}">{{ t('shop.inStock') }}</a>
                </div>
            </div>
        </aside>

        {{-- Product area --}}
        <div id="product-area">
            {{-- Sort chips + view toggle row --}}
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
                <div style="display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;">
                    <button id="filter-toggle-btn"
                            style="display:flex;align-items:center;gap:.35rem;border:1px solid var(--line);background:var(--paper);border-radius:999px;padding:.35rem .85rem;font-size:.75rem;color:var(--ink-soft);cursor:pointer;font-family:inherit;transition:all 180ms ease;"
                            onclick="toggleFilters()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54z"/></svg>
                        {{ t('shop.filters') }}{{ $activeCount ? ' ('.$activeCount.')' : '' }}
                    </button>
                    <span style="width:1px;height:16px;background:var(--line);display:inline-block;margin:0 .25rem;"></span>
                    @foreach($sortKeys as [$sVal, $sLabel])
                    @php
                        $sP = $q; $sP['sort'] = $sVal; unset($sP['page']);
                    @endphp
                        <a href="/shop?{{ http_build_query($sP) }}"
                           class="chip{{ $srtAct === $sVal ? ' chip-active' : '' }}">{{ $sLabel }}</a>
                    @endforeach
                </div>

                {{-- Grid / List toggle --}}
                @php $vqGrid = $q; $vqGrid['view'] = 'grid'; unset($vqGrid['page']); $vqList = $q; $vqList['view'] = 'list'; unset($vqList['page']); @endphp
                <div style="display:flex;gap:.25rem;">
                    <a href="/shop?{{ http_build_query($vqGrid) }}"
                       style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid var(--line);border-radius:4px;color:{{ $view==='grid' ? 'var(--ink)' : 'var(--muted)' }};background:{{ $view==='grid' ? 'var(--canvas)' : 'var(--paper)' }};"
                       aria-label="Grid view">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    </a>
                    <a href="/shop?{{ http_build_query($vqList) }}"
                       style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid var(--line);border-radius:4px;color:{{ $view==='list' ? 'var(--ink)' : 'var(--muted)' }};background:{{ $view==='list' ? 'var(--canvas)' : 'var(--paper)' }};"
                       aria-label="List view">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </a>
                </div>
            </div>

            {{-- Count --}}
            <p style="font-size:.78rem;color:var(--muted);margin-bottom:1.5rem;">
                {{ $total }} {{ $total === 1 ? t('shop.piece') : t('shop.pieces') }}
            </p>

            @if(count($products) === 0)
                <div class="card" style="padding:3rem;text-align:center;">
                    <h2 class="display" style="font-size:1.3rem;">{{ t('shop.noneTitle') }}</h2>
                    <p style="margin-top:.6rem;font-size:.85rem;color:var(--ink-soft);">{{ t('shop.noneBody') }}</p>
                    <a class="btn btn-secondary btn-sm" style="margin-top:1.2rem;display:inline-flex;" href="{{ route('shop') }}">{{ t('shop.clearFilters') }}</a>
                </div>
            @else
                @if($view === 'list')
                    {{-- List view --}}
                    <div style="display:flex;flex-direction:column;gap:1rem;">
                        @foreach($products as $p)
                        <a href="{{ route('product.show', $p['slug']) }}"
                           style="display:flex;gap:1.25rem;padding:1rem;background:var(--paper);border:1px solid var(--line);border-radius:8px;text-decoration:none;transition:border-color 180ms ease;"
                           onmouseover="this.style.borderColor='var(--ink)'" onmouseout="this.style.borderColor='var(--line)'">
                            <div class="photo-frame" style="width:80px;height:107px;flex-shrink:0;border-radius:8px;overflow:hidden;position:relative;background:var(--accent-soft);">
                                @if($p['image'])
                                    <img src="{{ $p['image'] }}" alt="{{ $p['imageAlt'] ?? '' }}" style="width:100%;height:100%;object-fit:cover;object-position:top;">
                                @endif
                            </div>
                            <div style="flex:1;min-width:0;">
                                <h3 style="font-size:.95rem;font-weight:400;color:var(--ink);">{{ \App\Support\I18n::pick($p['name'], $p['name_fr'] ?? '') }}</h3>
                                <p style="font-size:.75rem;color:var(--muted);margin-top:.2rem;">{{ \App\Support\I18n::pick($p['collectionName'] ?? ($p['categoryName'] ?? 'OSSZ'), $p['collectionNameFr'] ?? ($p['categoryNameFr'] ?? null)) }}</p>
                                <p style="margin-top:.5rem;font-size:.88rem;color:var(--ink-soft);">{{ format_xaf($p['basePrice']) }}</p>
                                @if(!($p['inStock'] ?? true))
                                    <span style="display:inline-block;margin-top:.4rem;font-size:.62rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);">{{ t('buy.soldOut') }}</span>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                @else
                    {{-- Grid view --}}
                    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(2,1fr);" id="product-grid">
                        @foreach($products as $p)
                            @include('partials.product-card', ['p' => $p])
                        @endforeach
                    </div>
                @endif

                {{-- Pagination --}}
                @if($totalPages > 1)
                <nav style="display:flex;justify-content:center;gap:.5rem;margin-top:3rem;" aria-label="Pagination">
                    @for($pg = 1; $pg <= $totalPages; $pg++)
                    @php $pp = $q; $pp['page'] = $pg; @endphp
                        <a href="/shop?{{ http_build_query($pp) }}"
                           style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:4px;font-size:.8rem;{{ $pg === $pageNo ? 'background:var(--ink);color:#fff;' : 'background:var(--paper);border:1px solid var(--line);color:var(--ink-soft);' }}">
                            {{ $pg }}
                        </a>
                    @endfor
                </nav>
                @endif
            @endif
        </div>{{-- /product-area --}}
    </div>{{-- /shop-layout --}}
</div>

@push('scripts')
<script>
var filtersOpen = false;

function toggleFilters() {
    filtersOpen = !filtersOpen;
    var sidebar = document.getElementById('filter-sidebar');
    var layout  = document.getElementById('shop-layout');
    if (filtersOpen) {
        sidebar.style.display = 'flex';
        if (window.innerWidth >= 1024) {
            layout.style.gridTemplateColumns = '260px 1fr';
        }
    } else {
        sidebar.style.display = 'none';
        layout.style.gridTemplateColumns = '1fr';
    }
}

// Auto-open filters on desktop if any are active
(function(){
    var active = {{ $activeCount }};
    if (active > 0 && window.innerWidth >= 1024) {
        toggleFilters();
    }
    // On desktop: 3-col product grid
    function applyGrid(){
        var grid = document.getElementById('product-grid');
        if (!grid) return;
        if (window.innerWidth >= 1024) {
            grid.style.gridTemplateColumns = filtersOpen ? 'repeat(3,1fr)' : 'repeat(4,1fr)';
        } else if (window.innerWidth >= 640) {
            grid.style.gridTemplateColumns = 'repeat(2,1fr)';
        } else {
            grid.style.gridTemplateColumns = '1fr';
        }
    }
    applyGrid();
    window.addEventListener('resize', applyGrid);
})();
</script>
@endpush

@endsection
