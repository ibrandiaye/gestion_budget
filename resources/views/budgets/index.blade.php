@extends('layouts.app')

@section('title', 'Gestion des Budgets Annuels')

@section('content')
    @if(session('success'))
        <div style="padding: 1rem; background: var(--success-light, #dcfce7); color: var(--success-dark, #166534); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div style="padding: 1rem; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            @foreach ($errors->all() as $error)
                <p style="margin: 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif
    @if(session('import_errors'))
        <div style="padding: 1rem; background: #fef3c7; border: 1px solid #fde68a; color: #92400e; border-radius: 0.5rem; margin-bottom: 1.5rem; max-height: 200px; overflow-y: auto;">
            <strong>⚠️ Erreurs d'importation :</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem;">
                @foreach(session('import_errors') as $err)
                    <li style="font-size: 0.85rem;">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Exercices Budgétaires</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted); font-size: 0.85rem;">
                        <th style="padding: 0.75rem;">Année</th>
                        <th style="padding: 0.75rem;">Statut</th>
                        <th style="padding: 0.75rem;">Période</th>
                        <th style="padding: 0.75rem;">Lignes</th>
                        <th style="padding: 0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                        <tr style="border-bottom: 1px solid var(--border-color); {{ $budget->statut == 'actif' ? 'background: var(--primary-50);' : '' }}">
                            <td style="padding: 0.75rem; font-weight: 700; font-size: 1.1rem;">
                                {{ $budget->annee }}
                            </td>
                            <td style="padding: 0.75rem;">
                                @if($budget->statut == 'actif')
                                    <span style="background: var(--success); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">ACTIF</span>
                                @elseif($budget->statut == 'brouillon')
                                    <span style="background: var(--warning); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">BROUILLON</span>
                                @else
                                    <span style="background: var(--slate-300); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">CLÔTURÉ</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; font-size: 0.85rem;">
                                {{ \Carbon\Carbon::parse($budget->date_debut)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($budget->date_fin)->format('d/m/Y') }}
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <span style="background: var(--slate-100); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; font-weight: 600;">
                                    {{ $budget->lignes_count ?? 0 }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem;">
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('budgets.show', $budget) }}" class="btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.75rem; text-decoration: none;">👁️ Voir</a>
                                    @if($budget->statut != 'actif')
                                        <form action="{{ route('budgets.activate', $budget) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">Activer</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Create -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">Ouvrir un nouvel exercice</h3>
                <form action="{{ route('budgets.store') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Année Budgétaire</label>
                        <input type="number" name="annee" value="{{ date('Y') + 1 }}" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date Début</label>
                        <input type="date" name="date_debut" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date Fin</label>
                        <input type="date" name="date_fin" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; box-sizing: border-box;">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;">Créer Budget</button>
                </form>
            </div>

            <!-- Import Budget from Excel -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">📥 Importer le Budget (Excel/CSV)</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">
                    Chargez un fichier CSV contenant les dotations budgétaires par compte et service.
                </p>

                <a href="{{ route('budgets.template') }}" class="btn-secondary" style="display: inline-block; margin-bottom: 1.5rem; text-decoration: none; padding: 0.5rem 1rem; font-size: 0.85rem;">
                    📄 Télécharger le modèle CSV
                </a>

                <form action="{{ route('budgets.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Exercice cible *</label>
                        <select name="budget_id" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; box-sizing: border-box;">
                            <option value="">Sélectionnez l'exercice...</option>
                            @foreach($budgets as $budget)
                                <option value="{{ $budget->id }}">{{ $budget->annee }} ({{ ucfirst($budget->statut) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fichier CSV *</label>
                        <input type="file" name="fichier" required accept=".csv,.xls,.xlsx"
                            style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: white; box-sizing: border-box;">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;" onclick="return confirm('Attention: cette action remplacera toutes les lignes budgétaires existantes pour l\'exercice sélectionné. Continuer ?');">📥 Importer le budget</button>
                </form>
            </div>
        </div>
    </div>
@endsection