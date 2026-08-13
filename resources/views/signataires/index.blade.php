@extends('layouts.app')

@section('title')Gestion des Signataires@endsection

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">

    {{-- Liste --}}
    <div class="card">
        <h3 style="margin-bottom:1.5rem;">Signataires enregistrés</h3>

        @if(session('success'))
            <div style="padding:0.75rem 1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1rem; font-size:0.9rem;">
                {{ session('success') }}
            </div>
        @endif

        @if($signataires->isEmpty())
            <p style="color:var(--text-muted); text-align:center; padding:2rem;">Aucun signataire enregistré.</p>
        @else
        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border-color); color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em;">
                    <th style="padding:0.6rem 0.75rem; text-align:left;">Nom complet</th>
                    <th style="padding:0.6rem 0.75rem; text-align:left;">Matricule</th>
                    <th style="padding:0.6rem 0.75rem; text-align:left;">Fonction</th>
                    <th style="padding:0.6rem 0.75rem; text-align:center;">Statut</th>
                    <th style="padding:0.6rem 0.75rem; text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($signataires as $s)
                <tr style="border-bottom:1px solid var(--border-color); {{ !$s->actif ? 'opacity:0.5;' : '' }}">
                    <td style="padding:0.75rem;">
                        <span style="font-weight:600;">{{ $s->prenom }} {{ $s->nom }}</span>
                    </td>
                    <td style="padding:0.75rem; font-family:monospace; color:var(--text-muted);">{{ $s->matricule ?? '—' }}</td>
                    <td style="padding:0.75rem;">{{ $s->fonction }}</td>
                    <td style="padding:0.75rem; text-align:center;">
                        @if($s->actif)
                            <span style="background:#dcfce7; color:#166534; padding:0.2rem 0.6rem; border-radius:99px; font-size:0.75rem; font-weight:600;">Actif</span>
                        @else
                            <span style="background:#f1f5f9; color:#64748b; padding:0.2rem 0.6rem; border-radius:99px; font-size:0.75rem; font-weight:600;">Inactif</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem; text-align:center;">
                        <div style="display:flex; gap:0.5rem; justify-content:center;">
                            {{-- Edit --}}
                            <button onclick="openEdit({{ $s->id }}, '{{ addslashes($s->prenom) }}', '{{ addslashes($s->nom) }}', '{{ addslashes($s->matricule) }}', '{{ addslashes($s->fonction) }}')"
                                style="background:var(--primary-50); color:var(--primary-700); border:none; padding:0.3rem 0.6rem; border-radius:4px; cursor:pointer; font-size:0.8rem;">✏️</button>
                            {{-- Toggle actif --}}
                            <form action="{{ route('signataires.toggle', $s) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:#fef3c7; color:#92400e; border:none; padding:0.3rem 0.6rem; border-radius:4px; cursor:pointer; font-size:0.8rem;" title="{{ $s->actif ? 'Désactiver' : 'Activer' }}">
                                    {{ $s->actif ? '⏸' : '▶️' }}
                                </button>
                            </form>
                            {{-- Delete --}}
                            <form action="{{ route('signataires.destroy', $s) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce signataire ?');">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:#fee2e2; color:#b91c1c; border:none; padding:0.3rem 0.6rem; border-radius:4px; cursor:pointer; font-size:0.8rem;">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Formulaire Ajout --}}
    <div>
        <div class="card" id="formCard">
            <h3 style="margin-bottom:1.5rem;" id="formTitle">➕ Nouveau Signataire</h3>

            <form id="sigForm" action="{{ route('signataires.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="_id" id="sigId">

                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-weight:500; margin-bottom:0.4rem; font-size:0.9rem;">Prénom *</label>
                    <input type="text" name="prenom" id="fieldPrenom" required class="form-input"
                        style="width:100%; padding:0.65rem; border:1px solid var(--border-color); border-radius:0.5rem; box-sizing:border-box;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-weight:500; margin-bottom:0.4rem; font-size:0.9rem;">Nom *</label>
                    <input type="text" name="nom" id="fieldNom" required
                        style="width:100%; padding:0.65rem; border:1px solid var(--border-color); border-radius:0.5rem; box-sizing:border-box;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-weight:500; margin-bottom:0.4rem; font-size:0.9rem;">Matricule</label>
                    <input type="text" name="matricule" id="fieldMatricule"
                        style="width:100%; padding:0.65rem; border:1px solid var(--border-color); border-radius:0.5rem; box-sizing:border-box;"
                        placeholder="Optionnel">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-weight:500; margin-bottom:0.4rem; font-size:0.9rem;">Fonction *</label>
                    <input type="text" name="fonction" id="fieldFonction" required
                        style="width:100%; padding:0.65rem; border:1px solid var(--border-color); border-radius:0.5rem; box-sizing:border-box;"
                        placeholder="Ex: Directeur, Gestionnaire…">
                </div>

                @if($errors->any())
                    <div style="background:#fee2e2; color:#b91c1c; padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem; font-size:0.85rem;">
                        @foreach($errors->all() as $e) <p style="margin:0;">{{ $e }}</p> @endforeach
                    </div>
                @endif

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn-primary" style="flex:1;" id="submitBtn">Enregistrer</button>
                    <button type="button" onclick="resetForm()" style="background:var(--slate-100); border:none; padding:0.65rem 1rem; border-radius:0.5rem; cursor:pointer; font-weight:500;">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEdit(id, prenom, nom, matricule, fonction) {
    document.getElementById('formTitle').textContent = '✏️ Modifier le Signataire';
    document.getElementById('fieldPrenom').value = prenom;
    document.getElementById('fieldNom').value = nom;
    document.getElementById('fieldMatricule').value = matricule || '';
    document.getElementById('fieldFonction').value = fonction;
    document.getElementById('sigId').value = id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('sigForm').action = '/signataires/' + id;
    document.getElementById('submitBtn').textContent = 'Mettre à jour';
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('formTitle').textContent = '➕ Nouveau Signataire';
    document.getElementById('sigForm').reset();
    document.getElementById('sigForm').action = '{{ route("signataires.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('submitBtn').textContent = 'Enregistrer';
}
</script>
@endsection
