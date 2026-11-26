@extends('layouts.app')

@section('title', is_fr() ? 'Erreur serveur' : 'Server error')

@section('content')
<div class="wrap" style="max-width:36rem;padding:5rem 1.25rem;text-align:center;">
    <p class="eyebrow">{{ is_fr() ? 'Oups' : 'Oh no' }}</p>
    <h1 class="display" style="margin-top:.5rem;font-size:2rem;">{{ is_fr() ? 'Une interruption est survenue' : 'Something interrupted us' }}</h1>
    <p style="margin-top:1rem;line-height:1.7;color:var(--ink-soft);">
        {{ is_fr()
            ? 'Une erreur inattendue s\'est produite et a été consignée pour l\'équipe technique. Réessayez dans un instant.'
            : 'An unexpected error occurred and has been logged for the team. Please try again in a moment.' }}
    </p>
    <div style="margin-top:2rem;display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('home') }}" class="btn btn-primary" style="min-width:10rem;">{{ is_fr() ? 'Retour à l\'accueil' : 'Back to home' }}</a>
    </div>
    <p style="margin-top:1.5rem;font-size:.75rem;color:var(--muted);">
        {{ is_fr() ? 'Besoin d\'aide immédiate ? WhatsApp +237 694 068 219.' : 'Need help right away? WhatsApp +237 694 068 219.' }}
    </p>
</div>
@endsection
