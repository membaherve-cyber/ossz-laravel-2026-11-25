@extends('layouts.app')

@section('title', is_fr() ? 'Connexion' : 'Sign in')

@section('content')
<div class="wrap" style="max-width:28rem;padding:5rem 1.25rem;">
    <p class="eyebrow">{{ is_fr() ? 'Bon retour' : 'Welcome back' }}</p>
    <h1 class="display" style="margin-top:.5rem;font-size:2.25rem;">{{ is_fr() ? 'Connexion' : 'Sign in' }}</h1>
    <p style="margin-top:.75rem;font-size:.875rem;line-height:1.6;color:var(--ink-soft);">
        {{ is_fr() 
            ? 'Un compte conserve vos adresses, l\'historique de vos commandes et votre liste d\'envies. Vous pouvez également commander en tant qu\'invité.' 
            : 'An account keeps your addresses, order history and wishlist to hand. You may always check out as a guest instead.' }}
    </p>

    <div style="margin-top:2rem;">
        <form method="POST" action="{{ route('login.attempt') }}" class="card" style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
            @csrf
            <input type="hidden" name="next" value="{{ $next }}">

            <div>
                <label class="label" for="email">{{ is_fr() ? 'Email ou identifiant' : 'Email or username' }}</label>
                <input class="field" id="email" name="email" type="text" required autocomplete="username" value="{{ old('email') }}" autofocus>
            </div>

            <div>
                <label class="label" for="password">{{ is_fr() ? 'Mot de passe' : 'Password' }}</label>
                <input class="field" id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <button class="btn btn-primary" type="submit" style="width:100%;margin-top:.5rem;">
                {{ is_fr() ? 'Se connecter' : 'Sign in' }}
            </button>

            @if (session('error'))
                <p style="font-size:.75rem;color:#dc2626;margin:0;">{{ session('error') }}</p>
            @endif

            <p style="text-align:center;font-size:.75rem;color:var(--muted);margin-top:.5rem;">
                <a href="{{ route('password.request') }}" class="link-underline" style="color:var(--accent);">
                    {{ is_fr() ? 'Mot de passe oublié ?' : 'Forgotten your password?' }}
                </a>
            </p>

            <p style="text-align:center;font-size:.75rem;color:var(--muted);">
                {{ is_fr() ? 'Nouveau client ?' : 'New here?' }}
                <a href="{{ route('register', ['next' => $next]) }}" class="link-underline" style="color:var(--accent);">
                    {{ is_fr() ? 'Créer un compte' : 'Create an account' }}
                </a>
            </p>
        </form>
    </div>
</div>
@endsection
