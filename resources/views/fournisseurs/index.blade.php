@extends('layouts.app')

@section('title', 'Gestion des Fournisseurs')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Liste des Fournisseurs -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">🏪 Fournisseurs</h3>
            
            @if($fournisseurs->isEmpty())
                <p style="color: var(--text-muted); text-align: center; padding: 2rem;">Aucun fournisseur enregistré.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($fournisseurs as $fournisseur)
                    <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; overflow: hidden;">
                        <div style="background: var(--slate-50); padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="margin-bottom: 0.25rem;">
                                    <span style="font-weight: 700;">{{ $fournisseur->nom }}</span>
                                    <span style="font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.5rem; {{ $fournisseur->actif ? 'background: var(--success); color: white;' : 'background: var(--danger); color: white;' }}">
                                        {{ $fournisseur->actif ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.2rem;">
                                    @if($fournisseur->telephone || $fournisseur->telephone2)
                                        <span>
                                            📞 {{ $fournisseur->telephone }} 
                                            @if($fournisseur->telephone && $fournisseur->telephone2) / @endif
                                            {{ $fournisseur->telephone2 }}
                                        </span>
                                    @endif
                                    @if($fournisseur->banque)
                                        <span>
                                            🏦 {{ $fournisseur->banque }} 
                                            @if($fournisseur->numero_compte) - {{ $fournisseur->numero_compte }} @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button onclick="openEditModal({{ $fournisseur->toJson() }})" 
                                    style="border: none; background: none; color: var(--primary); cursor: pointer;">✏️</button>
                                <form action="{{ route('fournisseurs.destroy', $fournisseur) }}" method="POST" style="display: inline;" 
                                    onsubmit="return confirm('Supprimer ce fournisseur ?')">
                                    @csrf @method('DELETE')
                                    <button style="border: none; background: none; color: var(--text-muted); cursor: pointer;">🗑️</button>
                                </form>
                            </div>
                        </div>

                        <div style="padding: 0.75rem 1rem;">
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                <strong>Articles fournis:</strong> {{ $fournisseur->articles->count() }}
                            </p>
                            
                            @if($fournisseur->articles->count() > 0)
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: var(--slate-100);">
                                            <th style="padding: 0.4rem; text-align: left;">Article</th>
                                            <th style="padding: 0.4rem; text-align: left;">Compte</th>
                                            <th style="padding: 0.4rem; text-align: right;">Prix</th>
                                            <th style="padding: 0.4rem; text-align: center;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fournisseur->articles as $article)
                                        <tr style="border-top: 1px solid var(--border-color);">
                                            <td style="padding: 0.4rem;">{{ $article->designation }}</td>
                                            <td style="padding: 0.4rem;">{{ $article->compte->numero ?? '-' }}</td>
                                            <td style="padding: 0.4rem; text-align: right; font-weight: 600;">{{ number_format($article->pivot->prix, 0, ',', ' ') }} FCFA</td>
                                            <td style="padding: 0.4rem; text-align: center;">
                                                <form action="{{ route('fournisseurs.supprimer-article', [$fournisseur, $article]) }}" method="POST" style="display: inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="border: none; background: none; color: var(--danger); cursor: pointer; font-size: 0.8rem;">✗</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <!-- Ajouter article -->
                            <form action="{{ route('fournisseurs.ajouter-article', $fournisseur) }}" method="POST" 
                                style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-color);">
                                @csrf
                                <div style="display: flex; gap: 0.5rem; align-items: end;">
                                    <div style="flex: 2;">
                                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Article</label>
                                        <select name="article_id" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                                            <option value="">Sélectionner un article...</option>
                                            @foreach($articles as $art)
                                                @if(!$fournisseur->articles->contains($art->id))
                                                    <option value="{{ $art->id }}">{{ $art->compte->numero ?? '' }} - {{ $art->designation }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="flex: 1;">
                                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Prix (FCFA)</label>
                                        <input type="number" name="prix" required min="0" placeholder="0" 
                                            style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem; text-align: right;">
                                    </div>
                                    <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; white-space: nowrap;">
                                        ✓ Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Formulaire Nouveau Fournisseur -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">➕ Nouveau Fournisseur</h3>
            <form action="{{ route('fournisseurs.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom *</label>
                    <input type="text" name="nom" required placeholder="Raison sociale"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Téléphone 1</label>
                        <input type="text" name="telephone" placeholder="+221 77..."
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Téléphone 2</label>
                        <input type="text" name="telephone2" placeholder="+221 77..."
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" name="email" placeholder="contact@fournisseur.sn"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Adresse</label>
                    <input type="text" name="adresse" placeholder="Adresse complète"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">NINEA</label>
                    <input type="text" name="ninea" placeholder="Numéro NINEA"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Registre de Commerce</label>
                    <input type="text" name="registre_commerce" placeholder="RC..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Banque</label>
                        <input type="text" name="banque" placeholder="Nom de la banque"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro de compte</label>
                        <input type="text" name="numero_compte" placeholder="RIB / Numéro"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Ajouter le Fournisseur</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">✏️ Modifier Fournisseur</h3>
            <form id="editForm" method="POST">
                @csrf @method('PUT')

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom *</label>
                    <input type="text" name="nom" id="edit_nom" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Téléphone 1</label>
                        <input type="text" name="telephone" id="edit_telephone"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Téléphone 2</label>
                        <input type="text" name="telephone2" id="edit_telephone2"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" name="email" id="edit_email"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Adresse</label>
                    <input type="text" name="adresse" id="edit_adresse"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">NINEA</label>
                    <input type="text" name="ninea" id="edit_ninea"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Registre de Commerce</label>
                    <input type="text" name="registre_commerce" id="edit_registre_commerce"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Banque</label>
                        <input type="text" name="banque" id="edit_banque"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro de compte</label>
                        <input type="text" name="numero_compte" id="edit_numero_compte"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="actif" id="edit_actif" style="width: 1rem; height: 1rem;">
                        <span>Fournisseur actif</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeEditModal()" class="btn-secondary" style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(fournisseur) {
            document.getElementById('edit_nom').value = fournisseur.nom;
            document.getElementById('edit_telephone').value = fournisseur.telephone || '';
            document.getElementById('edit_telephone2').value = fournisseur.telephone2 || '';
            document.getElementById('edit_email').value = fournisseur.email || '';
            document.getElementById('edit_adresse').value = fournisseur.adresse || '';
            document.getElementById('edit_ninea').value = fournisseur.ninea || '';
            document.getElementById('edit_registre_commerce').value = fournisseur.registre_commerce || '';
            document.getElementById('edit_banque').value = fournisseur.banque || '';
            document.getElementById('edit_numero_compte').value = fournisseur.numero_compte || '';
            document.getElementById('edit_actif').checked = fournisseur.actif;

            let url = "{{ route('fournisseurs.update', ':id') }}";
            document.getElementById('editForm').action = url.replace(':id', fournisseur.id);
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
@endsection
