@extends('layouts.app')

@section('title', is_fr() ? 'Session expirée' : 'Session expired')

@section('content')
<div class="wrap" style="max-width:36rem;padding:5rem 1.25rem;text-align:center;">
    <p class="eyebrow">{{ is_fr() ? 'Sécurité' : 'Security' }}</p>
    <h1 class="display" style="margin-top:.5rem;font-size:2rem;">{{ is_fr() ? 'Page expirée' : 'Page expired' }}</h1>
    <p style="margin-top:1rem;line-height:1.7;color:var(--ink-soft);">
        {{ is_fr()
            ? 'Pour votre sécurité, cette page a expiré après une période d\'inactivité. Vos données n\'ont pas été envoyées en double — rechargez et réessayez.'
            : 'For your security this page expired after a period of inactivity. Nothing was submitted twice — reload and try again.' }}
    </p>
    <div style="margin-top:2rem;display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
        <a href="javascript:location.reload()" class="btn btn-primary" style="min-width:10rem;">{{ is_fr() ? 'Recharger la page' : 'Reload page' }}</a>
        <a href="{{ route('home') }}" class="btn" style="min-width:10rem;">{{ is_fr() ? 'Retour à l\'accueil' : 'Back to home' }}</a>
    </div>
    <p style="margin-top:1.5rem;font-size:.75rem;color:var(--muted);">
        {{ is_fr()
            ? 'Si le problème persiste, ouvrez la boutique dans son propre onglet et reconnectez-vous.'
            : 'If this keeps happening, open the boutique in its own tab and sign in there.' }}
    </p>
</div>
@endsection
