@extends('layouts.app')

@section('title', 'Catégories de Comptes (Parents)')

@section('content')
    <div style="display: flex; gap: 2rem;">
        <!-- List -->
        <div class="card" style="flex: 2;">
            <h3 style="margin-bottom: 1.5rem;">Liste des Catégories</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--primary-100);">
                        <th style="text-align: left; padding: 0.75rem;">Numéro</th>
                        <th style="text-align: left; padding: 0.75rem;">Libellé</th>
                        <th style="text-align: left; padding: 0.75rem;">Type</th>
                        <th style="text-align: right; padding: 0.75rem;">Comptes Liés</th>
                        <th style="text-align: right; padding: 0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parents as $parent)
                        <tr style="border-bottom: 1px solid var(--slate-100);">
                            <td style="padding: 0.75rem; font-weight: 700; color: var(--primary-700);">{{ $parent->numero }}</td>
                            <td style="padding: 0.75rem;">{{ $parent->libelle }}</td>
                            <td style="padding: 0.75rem;">
                                <span style="background: var(--slate-100); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    {{ $parent->type }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <span title="{{ $parent->comptes->pluck('numero')->join(', ') }}" style="cursor: help; border-bottom: 1px dotted var(--text-muted);">
                                    {{ $parent->comptes->count() }} compte(s)
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <button onclick="openModal({{ $parent }})" style="background: none; border: none; cursor: pointer; margin-right: 0.5rem;" title="Modifier">✏️</button>
                                
                                @if($parent->comptes->count() === 0)
                                    <form action="{{ route('compte-parents.destroy', $parent) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--danger);" title="Supprimer">🗑️</button>
                                    </form>
                                @else
                                    <span style="color: var(--text-muted); cursor: not-allowed;" title="Impossible de supprimer (comptes liés)">🗑️</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Create Form -->
        <div class="card" style="flex: 1; height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Ajouter une Catégorie</h3>
            <form action="{{ route('compte-parents.store') }}" method="POST">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro (ex: 10)</label>
                    <input type="text" name="numero" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé</label>
                    <input type="text" name="libelle" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Type</label>
                    <select name="type" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="Fonctionnement">Fonctionnement</option>
                        <option value="Investissement">Investissement</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Modifier Catégorie</h3>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro</label>
                    <input type="text" id="edit_numero" name="numero" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé</label>
                    <input type="text" id="edit_libelle" name="libelle" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Type</label>
                    <select id="edit_type" name="type" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="Fonctionnement">Fonctionnement</option>
                        <option value="Investissement">Investissement</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeModal()" class="btn-secondary" style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(data) {
            document.getElementById('edit_numero').value = data.numero;
            document.getElementById('edit_libelle').value = data.libelle;
            document.getElementById('edit_type').value = data.type;
            
            let url = "{{ route('compte-parents.update', ':id') }}";
            document.getElementById('editForm').action = url.replace(':id', data.id);
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
@endsection
