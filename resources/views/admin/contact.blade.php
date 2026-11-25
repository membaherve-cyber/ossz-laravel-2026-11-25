@extends('layouts.admin')

@section('title', is_fr() ? 'Boîte de Réception & Messages' : 'Contact Inbox & Inquiries')

@section('content')
<div style="display:grid;gap:1.25rem">
    @forelse ($messages as $message)
        <div class="card" style="padding:1.5rem;{{ $message->handled ? 'opacity:.75;' : '' }}">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.75rem;align-items:flex-start">
                <div>
                    <div style="display:flex;align-items:center;gap:.65rem">
                        <strong style="font-size:1rem;color:var(--ink)">{{ $message->name }}</strong>
                        <span class="status-badge {{ $message->handled ? 'success' : 'warning' }}">
                            {{ $message->handled ? (is_fr() ? 'Traité' : 'Handled') : (is_fr() ? 'En attente' : 'Awaiting Reply') }}
                        </span>
                    </div>
                    <div style="font-size:.78rem;color:var(--muted);margin-top:.25rem">
                        <span>{{ $message->contact }}</span> · <span>{{ format_date_time($message->created_at) }}</span>
                    </div>
                </div>

                @if (! $message->handled)
                    <form method="POST" action="{{ route('admin.contact.handled') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $message->id }}">
                        <button class="btn btn-secondary btn-sm" type="submit">
                            ✓ {{ is_fr() ? 'Marquer comme traité' : 'Mark as Handled' }}
                        </button>
                    </form>
                @endif
            </div>

            <!-- Client's message body -->
            <div style="margin-top:1rem;background:var(--canvas);padding:1rem;border-radius:12px;border:1px solid var(--line);font-size:.875rem;color:var(--ink-soft);white-space:pre-line;line-height:1.6">
                {{ $message->message }}
            </div>

            <!-- Reply Form -->
            @if (! $message->handled)
                <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--line)">
                    <p class="eyebrow" style="margin-bottom:.5rem">{{ is_fr() ? 'Envoyer une réponse par email / WhatsApp' : 'Direct Dispatch Reply' }}</p>
                    <form method="POST" action="{{ route('admin.contact.reply') }}" style="display:grid;gap:.75rem;max-width:640px">
                        @csrf
                        <input type="hidden" name="messageId" value="{{ $message->id }}">
                        <input class="field" name="subject" placeholder="{{ is_fr() ? 'Objet de l\'email (optionnel)' : 'Email subject line (optional)' }}" value="Re: {{ \Illuminate\Support\Str::limit($message->message, 40) }}">
                        <textarea class="field" name="reply" rows="3" placeholder="{{ is_fr() ? 'Rédigez votre réponse ici…' : 'Type your personalized message reply here…' }}" required></textarea>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <button class="btn btn-primary btn-sm" type="submit">
                                {{ is_fr() ? 'Envoyer la réponse' : 'Send Reply via SMTP / WhatsApp' }}
                            </button>
                            <span style="font-size:.72rem;color:var(--muted)">
                                {{ is_fr() ? 'Envoyé automatiquement par email ou file WhatsApp.' : 'Dispatched via Namecheap SMTP or queued to WhatsApp.' }}
                            </span>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="card" style="padding:3rem;text-align:center">
            <p style="color:var(--muted);font-size:.9rem">{{ is_fr() ? 'Aucun message dans la boîte de réception.' : 'No customer messages in the inbox.' }}</p>
        </div>
    @endforelse
</div>
@endsection
