@extends('layouts.admin')

@section('title', is_fr() ? 'Messages clients & archive' : 'Customer Messages & Archive')

@section('content')
<div class="card" style="padding:1.5rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h2 style="font-size:1.1rem;font-weight:500;color:var(--ink)">{{ is_fr() ? 'Journal des notifications & messages' : 'Notification & Message Log' }}</h2>
            <p style="font-size:.78rem;color:var(--muted);margin-top:.2rem">{{ is_fr() ? 'Historique complet des emails et messages WhatsApp envoyés ou en file d\'attente.' : 'Full historical audit of sent, queued, and failed client messages.' }}</p>
        </div>
        <span class="status-badge neutral" style="font-weight:600">{{ $notifications->count() }} {{ is_fr() ? 'enregistrements' : 'records' }}</span>
    </div>

    <div style="overflow-x:auto">
        <table class="table" style="width:100%;font-size:.825rem">
            <thead>
                <tr style="border-bottom:1px solid var(--line);text-align:left">
                    <th style="padding:.75rem 1rem">{{ is_fr() ? 'Canal' : 'Channel' }}</th>
                    <th style="padding:.75rem 1rem">{{ is_fr() ? 'Destinataire' : 'Recipient' }}</th>
                    <th style="padding:.75rem 1rem">{{ is_fr() ? 'Objet / Message' : 'Subject / Message' }}</th>
                    <th style="padding:.75rem 1rem">{{ is_fr() ? 'Statut' : 'Status' }}</th>
                    <th style="padding:.75rem 1rem">{{ is_fr() ? 'Date' : 'Date' }}</th>
                    <th style="padding:.75rem 1rem;text-align:right">{{ is_fr() ? 'Actions' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notifications as $n)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:.75rem 1rem">
                            <span class="status-badge {{ $n->channel === 'whatsapp' ? 'success' : 'neutral' }}">
                                {{ strtoupper($n->channel) }}
                            </span>
                        </td>
                        <td style="padding:.75rem 1rem;font-weight:500">{{ $n->recipient }}</td>
                        <td style="padding:.75rem 1rem;color:var(--ink-soft);max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $n->subject ?: \Illuminate\Support\Str::limit($n->body, 60) }}
                        </td>
                        <td style="padding:.75rem 1rem">
                            <span class="status-badge {{ $n->status === 'sent' ? 'success' : ($n->status === 'failed' ? 'danger' : 'warning') }}">
                                {{ $n->status }}
                            </span>
                        </td>
                        <td style="padding:.75rem 1rem;font-size:.75rem;color:var(--muted)">
                            {{ format_date_time($n->created_at) }}
                        </td>
                        <td style="padding:.75rem 1rem;text-align:right">
                            <div style="display:inline-flex;align-items:center;gap:.5rem;justify-content:flex-end">
                                @if ($n->channel === 'whatsapp')
                                    <a class="btn btn-secondary btn-sm" target="_blank" rel="noreferrer" href="{{ wa_link($n->recipient, $n->body) }}" style="padding:.3rem .6rem;font-size:.7rem">
                                        WhatsApp ↗
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" style="margin:0" onsubmit="return confirm('{{ is_fr() ? 'Supprimer cet enregistrement ?' : 'Permanently remove this notification record?' }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border:none;padding:.3rem .6rem;font-size:.7rem;cursor:pointer">
                                        {{ is_fr() ? 'Supprimer' : 'Delete' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:2rem;text-align:center;color:var(--muted)">
                            {{ is_fr() ? 'Aucune notification archivée.' : 'No notification records in archive.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
