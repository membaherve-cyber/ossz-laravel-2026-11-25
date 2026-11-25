@php
    $name = \App\Support\I18n::pick($p['name'], $p['name_fr'] ?? '');
    $img  = $p['image'] ?? '';
    $inStock = (bool)($p['inStock'] ?? true);
@endphp

<div class="group flex flex-col">
    <div class="relative">
        <a href="{{ route('product.show', $p['slug']) }}" class="block">
            <div class="photo-frame relative aspect-[3/4] overflow-hidden bg-accent-soft">
                @if ($img)
                    <img src="{{ $img }}" alt="{{ $p['imageAlt'] ?? $name }}" loading="lazy"
                         class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-300 group-hover:scale-[1.035]">
                @endif

                @if (!$inStock)
                    <span class="absolute left-3 top-3 rounded-full bg-paper/95 px-2.5 py-1 text-[0.62rem] tracking-[0.16em] uppercase">
                        {{ t('buy.soldOut') }}
                    </span>
                @endif

                <span class="quick-view-hint absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/50 to-transparent p-3 text-center text-[0.68rem] font-medium tracking-[0.16em] uppercase text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                    {{ t('buy.quickView') }}
                </span>
            </div>
        </a>
    </div>

    <a href="{{ route('product.show', $p['slug']) }}" class="mt-3 flex items-start justify-between gap-3 text-sm no-underline">
        <div>
            <h3 class="font-normal text-ink">{{ $name }}</h3>
            <p class="mt-1 text-xs text-muted">
                {{ \App\Support\I18n::pick($p['collectionName'] ?? ($p['categoryName'] ?? 'OSSZ'), ($p['collectionNameFr'] ?? ($p['categoryNameFr'] ?? null))) }}
            </p>
        </div>
        <p class="shrink-0 text-ink-soft whitespace-nowrap">{{ format_xaf($p['basePrice']) }}</p>
    </a>
</div>
