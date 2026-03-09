@extends('layouts.app')

@section('title', 'Plan Comptable Budgétaire')

@section('content')
    @if ($errors->any())
        <div
            style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                ⚠️ Erreur de Validation
            </div>
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center; justify-content: space-between;">
        <div>
            <h2 style="margin: 0;">Plan Comptable</h2>
            @if(isset($activeBudget))
                <div style="color: var(--primary); font-weight: 600; font-size: 0.9rem; margin-top: 0.25rem;">
                    Budget Actif : {{ $activeBudget->annee }} (Dotations modifiables pour cet exercice)
                </div>
            @else
                <div style="color: var(--danger); font-weight: 600; font-size: 0.9rem; margin-top: 0.25rem;">
                    ⚠️ Aucun budget actif. Les dotations ne seront pas liées.
                </div>
            @endif
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('comptes.template') }}" class="btn-secondary" style="text-decoration: none;">
                📥 Télécharger Modèle CSV
            </a>
            <button onclick="document.getElementById('importForm').style.display='flex'" class="btn-primary">
                📤 Importer CSV
            </button>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importForm" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Importer des Comptes</h3>
            <form action="{{ route('comptes.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fichier CSV</label>
                    <input type="file" name="file" accept=".csv,.txt" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                        Format accepté: CSV. Téléchargez le modèle pour voir le format requis.
                    </p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="document.getElementById('importForm').style.display='none'" 
                        class="btn-secondary" style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Importer</button>
                </div>
            </form>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List of Compte Parents and their Comptes -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Comptes Budgétaires</h3>
            
            @foreach($parents as $parent)
                <div style="margin-bottom: 2rem;">
                    <h4 style="color: var(--primary-700); border-bottom: 2px solid var(--primary-100); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        {{ $parent->numero }} - {{ $parent->libelle }}
                    </h4>
                    
                    <ul style="list-style: none; padding: 0;">
                        @foreach($parent->comptes as $compte)
                            <li style="margin-bottom: 0.5rem; border-bottom: 1px solid var(--slate-100); padding-bottom: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <span style="font-weight: 700; color: var(--slate-800); margin-right: 0.5rem;">{{ $compte->numero }}</span>
                                        <span style="font-weight: 600;">{{ $compte->libelle }}</span>
                                        
                                        @if($compte->is_marche)
                                            <span style="font-size: 0.7rem; background: var(--warning); color: white; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.5rem;">À MARCHÉ</span>
                                        @endif
                                        <span style="font-size: 0.75rem; margin-left: 0.5rem; color: {{ $compte->etat === 'actif' ? 'var(--success)' : 'var(--danger)' }};">
                                            {{ ucfirst($compte->etat) }}
                                        </span>
                                            <span style="font-size: 0.75rem; margin-left: 0.5rem; color: var(--primary); font-weight: 600;">
                                                Dotation {{ isset($activeBudget) ? $activeBudget->annee : '' }}: {{ number_format($compte->dotation_initiale, 0, ',', ' ') }} FCFA
                                            </span>
                                    </div>
                                    <div>
                                        <button data-compte="{{ json_encode($compte) }}" onclick="openCompteModal(JSON.parse(this.getAttribute('data-compte')))"
                                            style="border: none; background: none; color: var(--primary); cursor: pointer; font-size: 0.9rem; margin-right: 0.5rem;">✏️</button>
                                         <form action="{{ route('comptes.destroy', $compte) }}" method="POST" style="display: inline;"
                                            onsubmit="return confirm('Confirmer suppression ?')">
                                            @csrf @method('DELETE')
                                            <button
                                                style="border: none; background: none; color: var(--text-muted); cursor: pointer; font-size: 0.8rem;">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <!-- Parent total dotation -->
                    <div style="font-weight: 600; margin-top: 0.5rem;">
                        Total Dotation {{ $parent->numero }}: {{ number_format($parent->comptes->sum('dotation_initiale'), 0, ',', ' ') }} FCFA
                    </div>
                </div>
                @endforeach
        </div>


        <!-- Create Compte Form -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Nouveau Compte</h3>
            <form action="{{ route('comptes.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro de Compte</label>
                    <input type="text" name="numero" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé</label>
                    <input type="text" name="libelle" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte Parent</label>
                    <select name="compte_parent_id" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Sélectionner un parent...</option>
                        @foreach($allParents as $p)
                            <option value="{{ $p->id }}">{{ $p->numero }} - {{ $p->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_marche" style="width: 1rem; height: 1rem;">
                        <span style="font-weight: 500;">Compte à marché</span>
                    </label>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="fournisseur_requis" checked style="width: 1rem; height: 1rem;">
                        <span style="font-weight: 500;">Fournisseur requis</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">(décocher pour salaires, etc.)</span>
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Dotation Initiale</label>
                        <input type="number" name="dotation_initiale" value="0" step="0.01"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Etat</label>
                    <select name="etat" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Ajouter Compte</button>
            </form>
        </div>
    </div>
    </div>

    <!-- Edit Modal -->
    <div id="compteModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Modifier Compte</h3>
            <form id="editCompteForm" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro de Compte</label>
                    <input type="text" id="edit_numero" name="numero" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé</label>
                    <input type="text" id="edit_libelle" name="libelle" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte Parent</label>
                     <select id="edit_compte_parent_id" name="compte_parent_id" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Sélectionner un parent...</option>
                        @foreach($allParents as $p)
                            <option value="{{ $p->id }}">{{ $p->numero }} - {{ $p->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="edit_is_marche" name="is_marche" style="width: 1rem; height: 1rem;">
                        <span style="font-weight: 500;">Compte à marché</span>
                    </label>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="edit_fournisseur_requis" name="fournisseur_requis" style="width: 1rem; height: 1rem;">
                        <span style="font-weight: 500;">Fournisseur requis</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">(décocher pour salaires, etc.)</span>
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Dotation Initiale</label>
                        <input type="number" id="edit_dotation_initiale" name="dotation_initiale" step="0.01"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Etat</label>
                    <select id="edit_etat" name="etat" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeCompteModal()" class="btn-secondary"
                        style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCompteModal(compte) {
            console.log('Opening modal for:', compte);
            try {
                const modal = document.getElementById('compteModal');
                const form = document.getElementById('editCompteForm');

                if (!modal || !form) {
                    console.error('Modal or form not found');
                    return;
                }

                document.getElementById('edit_numero').value = compte.numero;
                document.getElementById('edit_libelle').value = compte.libelle;
                // document.getElementById('edit_type').value = compte.type; // Removed
                if (document.getElementById('edit_is_marche')) document.getElementById('edit_is_marche').checked = compte.is_marche || false;
                if (document.getElementById('edit_fournisseur_requis')) document.getElementById('edit_fournisseur_requis').checked = compte.fournisseur_requis !== false;
                document.getElementById('edit_etat').value = compte.etat || 'actif';
                
                // Dotation fields might be missing if 0, handle safely
                if (document.getElementById('edit_dotation_initiale')) document.getElementById('edit_dotation_initiale').value = compte.dotation_actuelle || compte.dotation_initiale || 0;
                if (document.getElementById('edit_prix_unitaire_ref')) document.getElementById('edit_prix_unitaire_ref').value = compte.prix_ref_actuel || compte.prix_unitaire_ref || 0;
                
                document.getElementById('edit_compte_parent_id').value = compte.compte_parent_id || '';

                let updateUrl = "{{ route('comptes.update', ':id') }}";
                form.action = updateUrl.replace(':id', compte.id);
                modal.style.display = 'flex';
            } catch (e) {
                console.error('Error in openCompteModal:', e);
                alert('Erreur lors de l\'ouverture de la modification: ' + e.message);
            }
        }

        function closeCompteModal() {
            document.getElementById('compteModal').style.display = 'none';
        }

        document.getElementById('compteModal').addEventListener('click', function (e) {
            if (e.target === this) closeCompteModal();
        });
    </script>
@endsection