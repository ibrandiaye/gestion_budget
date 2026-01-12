@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List of Users -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Utilisateurs Actifs</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                        <th style="padding: 0.75rem;">Nom</th>
                        <th style="padding: 0.75rem;">Role</th>
                        <th style="padding: 0.75rem;">Service</th>
                        <th style="padding: 0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem;">
                                <div style="font-weight: 500;">{{ $user->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $user->email }}</div>
                            </td>
                            <td style="padding: 0.75rem;">
                                <span
                                    style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 99px; background: var(--slate-100);">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; color: var(--text-muted);">
                                {{ $user->service->nom ?? '-' }}
                            </td>
                            <td style="padding: 0.75rem;">
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            style="color: var(--danger); background: none; border: none; cursor: pointer;">🗑️</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Create User Form -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Nouveau Compte</h3>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom Complet</label>
                    <input type="text" name="name" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Mot de passe</label>
                    <input type="password" name="password" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Rôle</label>
                    <select name="role" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="service">Chef de Service</option>
                        <option value="controleur">Contrôleur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Service (Si applicable)</label>
                    <select name="service_id"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Aucun</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Créer l'utilisateur</button>
            </form>
        </div>
    </div>
@endsection