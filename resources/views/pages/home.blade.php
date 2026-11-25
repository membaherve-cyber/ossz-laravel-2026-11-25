@extends('layouts.app')

@section('title', 'OSSZ Collections — Contemporary fashion house, Douala')

@section('content')
    @php $loc = locale(); @endphp

    {{-- Hero --}}
    <section class="relative w-full overflow-hidden bg-[#1a1a1a]" style="height:90vh;min-height:600px;">
        @if ($hero && $hero->image_url)
            <img src="{{ $hero->image_url }}" alt="{{ $hero->heading }}" fetchpriority="high"
                 class="hero-zoom object-contain object-center opacity-90 absolute inset-0 w-full h-full">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent pointer-events-none"></div>
        <div class="wrap relative flex h-full flex-col items-center justify-end pb-10 text-center text-white md:pb-16">
            <h1 class="display rise delay-1 max-w-3xl text-lg leading-[1.15] md:text-3xl">
                {{ \App\Support\I18n::pick($hero->heading ?? '', $hero->heading_fr ?? '') }}
            </h1>

            <div class="rise delay-3 mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $hero->cta_href ?? '/shop' }}" class="btn btn-primary bg-white text-ink hover:bg-accent hover:text-white">
                    {{ \App\Support\I18n::pick($hero->cta_label ?? '', $hero->cta_label_fr ?? '') }}
                </a>
                <a href="{{ route('appointments') }}" class="btn bg-ink text-white hover:bg-accent hover:text-white border border-white/20">
                    {{ t('home.bookCta') }}
                </a>
            </div>
        </div>
    </section>

    {{-- New arrivals --}}
    <section class="wrap py-20">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <p class="eyebrow">{{ t('home.newEyebrow') }}</p>
                <h2 class="display mt-2 text-3xl md:text-4xl">{{ t('home.newTitle') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-soft">{{ t('home.newIntro') }}</p>
            </div>
            <a href="{{ route('shop') }}" class="link-underline text-[0.75rem] tracking-[0.16em] uppercase text-ink-soft">
                {{ t('home.shopAll') }} →
            </a>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-10 lg:grid-cols-4">
            @foreach ($newArrivals as $p)
                @include('partials.product-card', ['p' => $p])
            @endforeach
        </div>
    </section>

    {{-- Collection banner --}}
    @if ($banner)
        <section class="relative overflow-hidden bg-[#1a1a1a]">
            <div class="grid md:grid-cols-2">
                <div class="relative aspect-[4/3] md:aspect-auto md:min-h-[520px]">
                    @if ($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->heading }}"
                             class="object-cover object-top absolute inset-0 w-full h-full">
                    @endif
                </div>
                <div class="flex flex-col justify-center gap-5 px-8 py-16 text-white md:px-16">
                    <p class="eyebrow text-white/70">{{ \App\Support\I18n::pick($banner->eyebrow, $banner->eyebrow_fr) }}</p>
                    <h2 class="display text-3xl md:text-4xl">{{ \App\Support\I18n::pick($banner->heading, $banner->heading_fr) }}</h2>
                    <p class="max-w-md text-sm leading-relaxed text-white/80">{{ \App\Support\I18n::pick($banner->body, $banner->body_fr) }}</p>
                    <a href="{{ $banner->cta_href }}"
                       class="btn btn-secondary w-fit border-white text-white hover:bg-white hover:text-ink">
                        {{ \App\Support\I18n::pick($banner->cta_label, $banner->cta_label_fr) }}
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- Featured --}}
    <section class="wrap py-20">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <p class="eyebrow">{{ t('home.editEyebrow') }}</p>
                <h2 class="display mt-2 text-3xl md:text-4xl">{{ t('home.editTitle') }}</h2>
            </div>
            <a href="{{ route('shop', ['sort' => 'popular']) }}" class="link-underline text-[0.75rem] tracking-[0.16em] uppercase text-ink-soft">
                {{ t('home.editLink') }} →
            </a>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-10 lg:grid-cols-4">
            @foreach ($featured as $p)
                @include('partials.product-card', ['p' => $p])
            @endforeach
        </div>
    </section>

    {{-- Collections --}}
    <section class="bg-paper py-20">
        <div class="wrap">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="max-w-2xl">
                    <p class="eyebrow">{{ t('home.colsEyebrow') }}</p>
                    <h2 class="display mt-2 text-3xl md:text-4xl">{{ t('home.colsTitle') }}</h2>
                </div>
                <a href="{{ route('collections.index') }}" class="link-underline text-[0.75rem] tracking-[0.16em] uppercase text-ink-soft">
                    {{ t('home.colsLink') }} →
                </a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($collections as $c)
                    <a href="{{ route('collections.show', $c->slug) }}" class="group zoom-parent block">
                        <div class="photo-frame relative aspect-[4/5]">
                            @if ($c->cover_image)
                                <img src="{{ $c->cover_image }}" alt="{{ $c->name }}" loading="lazy"
                                     class="object-cover object-top absolute inset-0 w-full h-full">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                                <p class="eyebrow text-white/70">{{ \App\Support\I18n::pick($c->season, $c->season_fr) }}</p>
                                <h3 class="display mt-1 text-2xl">{{ \App\Support\I18n::pick($c->name, $c->name_fr) }}</h3>
                            </div>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-ink-soft">{{ \App\Support\I18n::pick($c->description, $c->description_fr) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Lookbook teaser --}}
    <section class="wrap py-20">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <p class="eyebrow">{{ t('home.lookEyebrow') }}</p>
                <h2 class="display mt-2 text-3xl md:text-4xl">{{ t('home.lookTitle') }}</h2>
            </div>
            <a href="{{ route('lookbook') }}" class="link-underline text-[0.75rem] tracking-[0.16em] uppercase text-ink-soft">
                {{ t('home.lookLink') }} →
            </a>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3">
            @foreach ($looks as $index => $look)
                <figure class="{{ $index === 0 ? 'col-span-2 md:col-span-1' : '' }}">
                    <div class="photo-frame relative aspect-[3/4] zoom-parent">
                        @if ($look->media_type === 'video' && $look->video_url)
                            <video src="{{ $look->video_url }}" poster="{{ $look->poster_url }}" muted playsinline preload="metadata"
                                   class="object-cover object-top absolute inset-0 w-full h-full"></video>
                        @elseif ($look->image_url)
                            <img src="{{ $look->image_url }}" alt="{{ $look->title }}" loading="lazy"
                                 class="object-cover object-top absolute inset-0 w-full h-full">
                        @endif
                    </div>
                    <figcaption class="mt-2 text-xs text-muted">{{ \App\Support\I18n::pick($look->caption, $look->caption_fr) }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    {{-- Brand story + journal --}}
    <section class="bg-accent-soft/40 py-20">
        <div class="wrap grid items-center gap-12 md:grid-cols-2">
            <div>
                <p class="eyebrow">{{ t('home.storyEyebrow') }}</p>
                <h2 class="display mt-3 text-3xl md:text-4xl">{{ t('home.storyTitle') }}</h2>
                <p class="mt-5 text-sm leading-relaxed text-ink-soft">{{ t('home.storyBody') }}</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('about') }}" class="btn btn-secondary">{{ t('home.storyCta') }}</a>
                    <a href="{{ route('journal.index') }}" class="btn btn-ghost">{{ t('home.journalCta') }}</a>
                </div>
            </div>
            <div class="grid gap-5">
                @foreach ($posts as $post)
                    <a href="{{ route('journal.show', $post->slug) }}" class="card flex gap-4 p-4 hover:border-ink">
                        <div class="photo-frame photo-frame-sm relative h-24 w-24 shrink-0">
                            @if ($post->cover_image)
                                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" loading="lazy"
                                     class="object-cover object-top absolute inset-0 w-full h-full">
                            @endif
                        </div>
                        <div>
                            <h3 class="display text-xl leading-tight">{{ \App\Support\I18n::pick($post->title, $post->title_fr) }}</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-soft">{{ \App\Support\I18n::pick($post->excerpt, $post->excerpt_fr) }}</p>
                        </div>
                    </a>
                @endforeach
                @if ($invite)
                    <div class="card bg-paper p-5">
                        <p class="eyebrow">{{ \App\Support\I18n::pick($invite->eyebrow, $invite->eyebrow_fr) }}</p>
                        <h3 class="display mt-1 text-xl">{{ \App\Support\I18n::pick($invite->heading, $invite->heading_fr) }}</h3>
                        <p class="mt-2 text-xs text-ink-soft">{{ \App\Support\I18n::pick($invite->body, $invite->body_fr) }}</p>
                        <a href="{{ $invite->cta_href }}" class="btn btn-primary btn-sm mt-4">
                            {{ \App\Support\I18n::pick($invite->cta_label, $invite->cta_label_fr) }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Bottom contact CTA --}}
    <section class="bg-[#1a1a1a] py-10">
        <div class="wrap flex flex-col items-center justify-center gap-3 text-center">
            <p class="text-sm text-white/90">
                {{ is_fr() ? 'Besoin d\'aide pour trouver la bonne pièce ? Nous sommes là pour vous.' : 'Need help finding the right piece, or want to write us a message? We are here for you.' }}
            </p>
            <a href="{{ route('contact') }}" class="btn bg-white text-ink hover:bg-accent hover:text-white">
                {{ is_fr() ? 'Nous contacter' : 'Get in touch' }}
            </a>
            <p class="text-xs text-white/50">
                {{ is_fr() ? 'Ou écrivez-nous à' : 'Or write to us directly at' }}
                <a href="mailto:{{ \App\Support\Settings::get('contact_email') }}" class="text-accent hover:text-white hover:underline">
                    {{ \App\Support\Settings::get('contact_email') }}
                </a>
            </p>
        </div>
    </section>
@endsection
