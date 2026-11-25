<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Studio Administration') · OSSZ Collections</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/poppins?family=300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
@php
    $user = $osszUser ?? \App\Models\User::find(session('user_id'));
    $canFulfil = \App\Support\Auth::canFulfilOrders($user->role ?? 'customer');
    $canManage = \App\Support\Auth::canManageCatalogue($user->role ?? 'customer');
    $isAdmin = \App\Support\Auth::isAdmin($user->role ?? 'customer');
    $current = request()->route() ? request()->route()->getName() : '';
    
    $initials = 'OS';
    if ($user) {
        $nameParts = explode(' ', $user->full_name ?: $user->email);
        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
    }
@endphp

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div>
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="brand-logo">OSSZ</div>
                <div>
                    <div class="brand-title">OSSZ Collections</div>
                    <div class="brand-sub">Studio Back-Office</div>
                </div>
            </div>

            <!-- Profile Capsule -->
            <div class="profile-capsule">
                <div class="profile-avatar">{{ $initials }}</div>
                <div style="min-width:0;flex:1">
                    <div class="profile-name">{{ $user->full_name ?? ($user->email ?? 'Administrator') }}</div>
                    <div class="profile-role">● {{ strtoupper($user->role ?? 'admin') }}</div>
                </div>
            </div>

            <!-- Nav Groups -->
            <nav>
                @if ($canFulfil)
                    <span class="nav-group-title">Operations</span>
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ str_starts_with($current, 'admin.dashboard') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>{{ is_fr() ? 'Tableau de bord' : 'Dashboard' }}</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="nav-item {{ str_starts_with($current, 'admin.orders') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>{{ is_fr() ? 'Commandes' : 'Orders queue' }}</span>
                    </a>
                    <a href="{{ route('admin.inventory') }}" class="nav-item {{ $current === 'admin.inventory' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>{{ is_fr() ? 'Inventaire' : 'Stock counts' }}</span>
                    </a>
                    <a href="{{ route('admin.appointments') }}" class="nav-item {{ $current === 'admin.appointments' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ t('nav.appointments') }}</span>
                    </a>
                    <a href="{{ route('admin.contact') }}" class="nav-item {{ $current === 'admin.contact' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ is_fr() ? 'Messages clients' : 'Customer messages' }}</span>
                    </a>
                    <a href="{{ route('admin.notifications') }}" class="nav-item {{ $current === 'admin.notifications' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span>{{ is_fr() ? 'Archive messages' : 'Message archive' }}</span>
                    </a>
                @endif

                @if ($canManage)
                    <span class="nav-group-title">Content</span>
                    <a href="{{ route('admin.products.index') }}" class="nav-item {{ str_starts_with($current, 'admin.products') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>{{ is_fr() ? 'Catalogue' : 'Products' }}</span>
                    </a>
                    <a href="{{ route('admin.collections.index') }}" class="nav-item {{ $current === 'admin.collections.index' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>{{ t('nav.collections') }}</span>
                    </a>
                    <a href="{{ route('admin.homepage') }}" class="nav-item {{ $current === 'admin.homepage' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ is_fr() ? 'Blocs accueil' : 'Homepage blocks' }}</span>
                    </a>
                    <a href="{{ route('admin.lookbook') }}" class="nav-item {{ $current === 'admin.lookbook' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ t('nav.lookbook') }}</span>
                    </a>
                    <a href="{{ route('admin.journal') }}" class="nav-item {{ $current === 'admin.journal' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>{{ t('nav.journal') }}</span>
                    </a>
                    <a href="{{ route('admin.media') }}" class="nav-item {{ $current === 'admin.media' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
                        <span>{{ is_fr() ? 'Médias' : 'Media library' }}</span>
                    </a>
                @endif

                @if ($isAdmin)
                    <span class="nav-group-title">Business</span>
                    <a href="{{ route('admin.customers') }}" class="nav-item {{ $current === 'admin.customers' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>{{ is_fr() ? 'Clients' : 'Customers' }}</span>
                    </a>
                    <a href="{{ route('admin.coupons') }}" class="nav-item {{ $current === 'admin.coupons' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>{{ is_fr() ? 'Codes promo' : 'Discounts' }}</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="nav-item {{ $current === 'admin.settings' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ is_fr() ? 'Réglages & conciergerie' : 'Settings & concierge' }}</span>
                    </a>
                    <a href="{{ route('admin.staff') }}" class="nav-item {{ $current === 'admin.staff' ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>{{ is_fr() ? 'Équipe & rôles' : 'Staff & roles' }}</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <a href="{{ route('home') }}" target="_blank" class="nav-item" style="color:var(--muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>{{ is_fr() ? 'Voir la boutique' : 'Live storefront' }} ↗</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button class="nav-item" type="submit" style="width:100%;border:none;background:none;cursor:pointer;color:var(--muted);text-align:left">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>{{ is_fr() ? 'Se déconnecter' : 'Sign out' }}</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <p class="eyebrow">{{ is_fr() ? 'Espace administration' : 'Back office' }}</p>
                <h1 style="margin-top:.2rem">@yield('title', 'Dashboard')</h1>
            </div>
            @yield('actions')
        </div>

        @if (session('success'))
            <div class="card status-badge success" style="display:block;margin:0 0 1.5rem;padding:0.75rem 1rem;font-size:0.825rem;border-radius:12px">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="card status-badge danger" style="display:block;margin:0 0 1.5rem;padding:0.75rem 1rem;font-size:0.825rem;border-radius:12px">
                ✗ {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="card status-badge danger" style="display:block;margin:0 0 1.5rem;padding:0.75rem 1rem;font-size:0.825rem;border-radius:12px">
                {{ implode(' · ', $errors->all()) }}
            </div>
        @endif

        @yield('content')
    </div>
</div>
@stack('scripts')
</body>
</html>
