@extends('layouts.admin')

@section('title', is_fr() ? 'Équipe & Gestion des Rôles' : 'Staff & Roles Management')

@section('content')
<div style="display:grid;grid-template-columns:1fr;gap:1.5rem">
    @if ($errors->any())
        <div class="card status-badge danger" style="display:block;padding:0.75rem 1rem;font-size:0.825rem;border-radius:12px">
            {{ implode(' · ', $errors->all()) }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1.5rem;align-items:start">
        <!-- Staff List Table -->
        <div class="card" style="padding:1.5rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
                <div>
                    <h2 style="font-size:1.1rem;font-weight:500;color:var(--ink)">{{ is_fr() ? 'Comptes d\'administration actifs' : 'Active Staff Accounts' }}</h2>
                    <p style="font-size:.78rem;color:var(--muted);margin-top:.2rem">{{ is_fr() ? 'Membres disposant d\'un accès au back-office.' : 'Users with back-office permissions.' }}</p>
                </div>
                <span class="status-badge neutral">{{ $staff->count() }} {{ is_fr() ? 'membres' : 'members' }}</span>
            </div>

            <table class="table" style="width:100%;font-size:.825rem">
                <thead>
                    <tr style="border-bottom:1px solid var(--line);text-align:left">
                        <th style="padding:.75rem 1rem">Staff</th>
                        <th style="padding:.75rem 1rem">Email</th>
                        <th style="padding:.75rem 1rem">{{ is_fr() ? 'Rôle' : 'Role' }}</th>
                        <th style="padding:.75rem 1rem;text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        @php
                            $memberInitials = strtoupper(substr($member->full_name ?: $member->email, 0, 2));
                        @endphp
                        <tr style="border-bottom:1px solid var(--line)">
                            <td style="padding:.75rem 1rem">
                                <div style="display:flex;align-items:center;gap:.65rem">
                                    <div style="width:32px;height:32px;border-radius:8px;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600">
                                        {{ $memberInitials }}
                                    </div>
                                    <div>
                                        <div style="font-weight:500;color:var(--ink)">{{ $member->full_name ?: '—' }}</div>
                                        @if($member->username)<div style="font-size:.7rem;color:var(--muted)">@{{ $member->username }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:.75rem 1rem;color:var(--ink-soft)">{{ $member->email }}</td>
                            <td style="padding:.75rem 1rem">
                                <span class="status-badge {{ $member->role === 'admin' ? 'success' : 'neutral' }}">
                                    {{ strtoupper($member->role) }}
                                </span>
                            </td>
                            <td style="padding:.75rem 1rem;text-align:right">
                                <details style="position:relative;display:inline-block">
                                    <summary class="btn btn-secondary btn-sm" style="padding:.3rem .65rem;font-size:.7rem;cursor:pointer">
                                        {{ is_fr() ? 'Modifier' : 'Edit' }} ▼
                                    </summary>
                                    <div class="card" style="position:absolute;right:0;top:calc(100% + 5px);z-index:40;width:280px;padding:1.25rem;text-align:left;box-shadow:0 10px 25px rgba(0,0,0,0.1)">
                                        <h4 style="font-size:.85rem;font-weight:600;margin-bottom:.75rem">{{ is_fr() ? 'Modifier le compte' : 'Edit Account' }}</h4>
                                        <form method="POST" action="{{ route('admin.staff.update', $member) }}" style="display:grid;gap:.65rem">
                                            @csrf
                                            <div>
                                                <label class="label" style="font-size:.65rem">Full Name</label>
                                                <input class="field" style="padding:.4rem .6rem;font-size:.75rem" name="fullName" value="{{ $member->full_name }}" required>
                                            </div>
                                            <div>
                                                <label class="label" style="font-size:.65rem">Email</label>
                                                <input class="field" style="padding:.4rem .6rem;font-size:.75rem" type="email" name="email" value="{{ $member->email }}" required>
                                            </div>
                                            <div>
                                                <label class="label" style="font-size:.65rem">Role</label>
                                                <select class="field" style="padding:.4rem .6rem;font-size:.75rem" name="role">
                                                    <option value="uploader" @selected($member->role === 'uploader')>uploader</option>
                                                    <option value="staff" @selected($member->role === 'staff')>staff</option>
                                                    <option value="admin" @selected($member->role === 'admin')>admin</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="label" style="font-size:.65rem">New Password (optional)</label>
                                                <input class="field" style="padding:.4rem .6rem;font-size:.75rem" type="password" name="password" minlength="6" placeholder="Leave blank to keep">
                                            </div>
                                            <button class="btn btn-primary btn-sm" style="margin-top:.4rem;padding:.4rem .75rem" type="submit">
                                                {{ is_fr() ? 'Enregistrer' : 'Save Changes' }}
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="padding:2rem;text-align:center;color:var(--muted)">{{ is_fr() ? 'Aucun membre.' : 'No staff accounts.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Staff Form -->
        <div class="card" style="padding:1.5rem">
            <h3 style="font-size:1.05rem;font-weight:500;color:var(--ink);margin-bottom:.5rem">
                + {{ is_fr() ? 'Créer un nouveau compte' : 'Create Staff Member' }}
            </h3>
            <p style="font-size:.78rem;color:var(--muted);margin-bottom:1.25rem">
                {{ is_fr() ? 'Assignez les permissions appropriées pour l\'accès au catalogue ou aux commandes.' : 'Assign appropriate roles to grant catalogue or fulfillment access.' }}
            </p>
            <form method="POST" action="{{ route('admin.staff.store') }}" style="display:grid;gap:.85rem">
                @csrf
                <div>
                    <label class="label">{{ is_fr() ? 'Nom complet' : 'Full Name' }}</label>
                    <input class="field" name="fullName" placeholder="e.g. Marie Claire" required>
                </div>
                <div>
                    <label class="label">Email</label>
                    <input class="field" type="email" name="email" placeholder="staff@osszcollections.cm" required>
                </div>
                <div>
                    <label class="label">{{ is_fr() ? 'Identifiant court' : 'Username' }}</label>
                    <input class="field" name="username" placeholder="e.g. marie">
                </div>
                <div>
                    <label class="label">{{ is_fr() ? 'Rôle d\'accès' : 'Permission Role' }}</label>
                    <select class="field" name="role">
                        <option value="staff">Staff (Orders & Fulfilment)</option>
                        <option value="uploader">Uploader (Catalogue & Media)</option>
                        <option value="admin">Admin (Full Studio Access)</option>
                    </select>
                </div>
                <div>
                    <label class="label">{{ is_fr() ? 'Mot de passe initial' : 'Temporary Password (6+ chars)' }}</label>
                    <input class="field" type="password" name="password" minlength="6" placeholder="••••••••" required>
                </div>
                <button class="btn btn-primary btn-sm" style="margin-top:.5rem" type="submit">
                    {{ is_fr() ? 'Créer le compte' : 'Add Staff Member' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
