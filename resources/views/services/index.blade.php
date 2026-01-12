@extends('layouts.app')

@section('title', 'Gestion des Services')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List of Services -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Services Hospitaliers</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr
                        style="border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.85rem; color: var(--text-muted);">
                        <th style="padding: 0.75rem;">Nom</th>
                        <th style="padding: 0.75rem;">Code</th>
                        <th style="padding: 0.75rem;">Chef de Service</th>
                        <th style="padding: 0.75rem;">Département</th>
                        <th style="padding: 0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem; font-weight: 500;">
                                {{ $service->nom }}
                            </td>
                            <td style="padding: 0.75rem; font-family: monospace; font-size: 0.85rem;">
                                {{ $service->code }}
                            </td>
                            <td style="padding: 0.75rem; color: var(--text-muted); font-size: 0.9rem;">
                                {{ $service->chef_service ?? '-' }}
                            </td>
                            <td style="padding: 0.75rem; color: var(--text-muted); font-size: 0.9rem;">
                                {{ $service->departement ?? '-' }}
                            </td>
                            <td style="padding: 0.75rem;">
                                <form action="{{ route('services.destroy', $service) }}" method="POST"
                                    onsubmit="return confirm('Supprimer ce service ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="color: var(--danger); background: none; border: none; cursor: pointer;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-muted);">Aucun service
                                enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Create Service Form -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Nouveau Service</h3>
            <form action="{{ route('services.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom du Service</label>
                    <input type="text" name="nom" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Code (Identifiant)</label>
                    <input type="text" name="code" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Chef de Service</label>
                    <input type="text" name="chef_service"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Département</label>
                    <input type="text" name="departement"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Ajouter</button>
            </form>
        </div>
    </div>
@endsection