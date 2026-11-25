@php
    $osszUser  = $osszUser ?? (\App\Models\User::find(session('user_id')));
    $cartView  = \App\Support\Cart::view();
    $loc       = locale();
    $isAdmin   = $osszUser && \App\Support\Auth::canEnterBackOffice($osszUser->role);
@endphp

{{-- Free-delivery announcement strip --}}
@php $bannerText = t('banner.freeDelivery'); @endphp
@if(!empty($bannerText) && $bannerText !== 'banner.freeDelivery')
<div style="background:var(--ink);text-align:center;font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.85);padding:.5rem 1rem;">
    {{ $bannerText }}
</div>
@endif

<header class="site-header" id="site-header">
    <div class="wrap" style="display:flex;align-items:center;justify-content:space-between;height:64px;gap:1rem;position:relative;">

        {{-- Mobile hamburger --}}
        <button class="nav-toggle" id="nav-toggle" aria-label="{{ t('nav.openMenu') }}" aria-expanded="false"
                style="background:none;border:none;cursor:pointer;padding:.5rem;display:none;flex-direction:column;gap:5px;">
            <span style="display:block;height:1px;width:24px;background:var(--ink);"></span>
            <span style="display:block;height:1px;width:24px;background:var(--ink);"></span>
            <span style="display:block;height:1px;width:18px;background:var(--ink);"></span>
        </button>

        {{-- Left nav (desktop): Shop · Collections · Lookbook --}}
        <nav class="main-nav" id="main-nav" aria-label="Primary navigation"
             style="display:flex;align-items:center;gap:1.75rem;font-size:.78rem;letter-spacing:.14em;text-transform:uppercase;">
            <a href="{{ route('shop') }}"
               class="link-underline{{ request()->is('shop*') ? ' nav-active' : '' }}"
               style="color:{{ request()->is('shop*') ? 'var(--accent)' : 'var(--ink-soft)' }}">{{ t('nav.shop') }}</a>
            <a href="{{ route('collections.index') }}"
               class="link-underline{{ request()->is('collections*') ? ' nav-active' : '' }}"
               style="color:{{ request()->is('collections*') ? 'var(--accent)' : 'var(--ink-soft)' }}">{{ t('nav.collections') }}</a>
            <a href="{{ route('lookbook') }}"
               class="link-underline{{ request()->is('lookbook*') ? ' nav-active' : '' }}"
               style="color:{{ request()->is('lookbook*') ? 'var(--accent)' : 'var(--ink-soft)' }}">{{ t('nav.lookbook') }}</a>
        </nav>

        {{-- Centered logo --}}
        <a href="{{ route('home') }}" aria-label="OSSZ Collections"
           style="position:absolute;left:50%;transform:translateX(-50%);display:inline-block;line-height:0;flex-shrink:0;">
            <img src="/logo-ossz.png" alt="OSSZ Collections" style="height:32px;width:auto;display:block;">
        </a>

        {{-- Right nav (desktop): Journal · Appointments · About + utilities --}}
        <div style="display:flex;align-items:center;gap:1.75rem;">
            <nav class="main-nav main-nav-right" aria-label="Secondary navigation"
                 style="display:flex;align-items:center;gap:1.75rem;font-size:.78rem;letter-spacing:.14em;text-transform:uppercase;">
                <a href="{{ route('journal.index') }}"
                   class="link-underline{{ request()->is('journal*') ? ' nav-active' : '' }}"
                   style="color:{{ request()->is('journal*') ? 'var(--accent)' : 'var(--ink-soft)' }}">{{ t('nav.journal') }}</a>
                <a href="{{ route('appointments') }}"
                   class="link-underline{{ request()->is('appointments*') ? ' nav-active' : '' }}"
                   style="color:{{ request()->is('appointments*') ? 'var(--accent)' : 'var(--ink-soft)' }}">{{ t('nav.appointments') }}</a>
                <a href="{{ route('about') }}"
                   class="link-underline{{ request()->is('about*') ? ' nav-active' : '' }}"
                   style="color:{{ request()->is('about*') ? 'var(--accent)' : 'var(--ink-soft)' }}">{{ t('nav.about') }}</a>
            </nav>

            {{-- Utilities: lang · backoffice · account · cart --}}
            <div style="display:flex;align-items:center;gap:.9rem;font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;">
                {{-- Language switcher --}}
                <a href="{{ route('locale.switch', ['locale' => $loc === 'fr' ? 'en' : 'fr']) }}"
                   class="link-underline"
                   style="color:var(--ink-soft);font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;">
                    {{ $loc === 'fr' ? 'EN' : 'FR' }}
                </a>

                @if ($isAdmin)
                    <a href="{{ route('admin.dashboard') }}"
                       style="display:none;color:var(--ink-soft);"
                       class="backoffice-link link-underline">{{ t('nav.backoffice') }}</a>
                @endif

                <a href="{{ $osszUser ? route('account.home') : route('login') }}"
                   class="link-underline"
                   style="color:var(--ink-soft);white-space:nowrap;">
                    {{ $osszUser ? explode(' ', $osszUser->full_name ?: $osszUser->email)[0] : t('nav.signin') }}
                </a>

                {{-- Cart bag --}}
                <a href="{{ route('cart.show') }}" aria-label="{{ t('nav.cart') }}"
                   style="position:relative;color:var(--ink);display:inline-flex;align-items:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    @if ($cartView['itemCount'] > 0)
                        <span style="position:absolute;top:-6px;right:-6px;display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;border-radius:999px;background:var(--accent);color:#fff;font-size:.55rem;padding:0 .2rem;">
                            {{ $cartView['itemCount'] }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <nav id="mobile-nav"
         style="display:none;position:absolute;top:100%;left:0;right:0;background:var(--paper);border-bottom:1px solid var(--line);z-index:50;padding:1.25rem 1.5rem;flex-direction:column;gap:.85rem;">
        <a href="{{ route('shop') }}" style="font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">{{ t('nav.shop') }}</a>
        <a href="{{ route('collections.index') }}" style="font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">{{ t('nav.collections') }}</a>
        <a href="{{ route('lookbook') }}" style="font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">{{ t('nav.lookbook') }}</a>
        <a href="{{ route('journal.index') }}" style="font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">{{ t('nav.journal') }}</a>
        <a href="{{ route('appointments') }}" style="font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">{{ t('nav.appointments') }}</a>
        <a href="{{ route('about') }}" style="font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">{{ t('nav.about') }}</a>
        <div style="display:flex;gap:1rem;padding-top:.5rem;border-top:1px solid var(--line);">
            <a href="{{ route('locale.switch', ['locale' => $loc === 'fr' ? 'en' : 'fr']) }}" style="font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">
                {{ $loc === 'fr' ? 'EN' : 'FR' }}
            </a>
            <a href="{{ $osszUser ? route('account.home') : route('login') }}" style="font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);">
                {{ $osszUser ? explode(' ', $osszUser->full_name ?: $osszUser->email)[0] : t('nav.signin') }}
            </a>
        </div>
    </nav>
</header>

@if (session('success'))
    <div class="flash flash-success wrap">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="flash flash-error wrap">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="flash flash-error wrap">{{ implode(' · ', $errors->all()) }}</div>
@endif

@once
@push('scripts')
<script>
(function(){
    var btn = document.getElementById('nav-toggle');
    var nav = document.getElementById('mobile-nav');
    if(!btn || !nav) return;
    btn.addEventListener('click', function(){
        var open = nav.style.display === 'flex';
        nav.style.display = open ? 'none' : 'flex';
        btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
    // Scroll-shadow on header
    var hdr = document.getElementById('site-header');
    window.addEventListener('scroll', function(){
        hdr.style.borderBottomColor = window.scrollY > 12 ? 'var(--line)' : 'transparent';
    }, {passive:true});
})();
</script>
<style>
@media (max-width:900px){
    .main-nav, .main-nav-right, .backoffice-link { display:none !important; }
    #nav-toggle { display:flex !important; }
}
@media (min-width:901px){
    .backoffice-link { display:inline !important; }
}
</style>
@endpush
@endonce
