@extends('layouts.app')

@section('title', 'Gestion des Articles')

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

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List of Articles by Compte -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">📦 Articles par Compte</h3>
            
            @forelse($comptes as $compte)
                @if($compte->articles->count() > 0)
                <div style="margin-bottom: 1.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem; overflow: hidden;">
                    <div style="background: var(--slate-100); padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-weight: 700; color: var(--primary-700);">{{ $compte->numero }}</span>
                            <span style="font-weight: 600; margin-left: 0.5rem;">{{ $compte->libelle }}</span>
                            @if($compte->is_marche)
                                <span style="background: var(--warning); color: white; font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.5rem;">À MARCHÉ</span>
                            @endif
                        </div>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $compte->articles->count() }} article(s)</span>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--slate-50);">
                                <th style="padding: 0.5rem; text-align: left; font-size: 0.75rem; color: var(--text-muted);">DÉSIGNATION</th>
                                <th style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: var(--text-muted);">ÉTAT</th>
                                <th style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: var(--text-muted);">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compte->articles as $article)
                            <tr style="border-top: 1px solid var(--border-color);">
                                <td style="padding: 0.5rem; font-size: 0.85rem;">{{ $article->designation }}</td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <span style="font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 4px; {{ $article->actif ? 'background: var(--success); color: white;' : 'background: var(--danger); color: white;' }}">
                                        {{ $article->actif ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <button onclick="openArticleModal({{ $article->toJson() }})"
                                        style="border: none; background: none; color: var(--primary); cursor: pointer; font-size: 0.9rem; margin-right: 0.25rem;">✏️</button>
                                    <form action="{{ route('articles.destroy', $article) }}" method="POST" style="display: inline;"
                                        onsubmit="return confirm('Confirmer suppression ?')">
                                        @csrf @method('DELETE')
                                        <button style="border: none; background: none; color: var(--text-muted); cursor: pointer; font-size: 0.9rem;">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            @empty
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    Aucun article enregistré. Créez votre premier article →
                </div>
            @endforelse

            @if($comptes->every(fn($c) => $c->articles->count() === 0))
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    Aucun article enregistré. Créez votre premier article →
                </div>
            @endif
        </div>

        <!-- Create Article Form -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">➕ Nouvel Article</h3>
            <form action="{{ route('articles.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte *</label>
                    <select name="compte_id" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Sélectionner un compte...</option>
                        @foreach($allComptes as $c)
                            <option value="{{ $c->id }}">{{ $c->numero }} - {{ $c->libelle }} {{ $c->is_marche ? '(À marché)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Désignation *</label>
                    <input type="text" name="designation" required placeholder="Nom de l'article"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="actif" checked style="width: 1rem; height: 1rem;">
                        <span>Article actif</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Ajouter l'Article</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="articleModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">✏️ Modifier Article</h3>
            <form id="editArticleForm" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte *</label>
                    <select id="edit_compte_id" name="compte_id" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        @foreach($allComptes as $c)
                            <option value="{{ $c->id }}">{{ $c->numero }} - {{ $c->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Désignation *</label>
                    <input type="text" id="edit_designation" name="designation" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="edit_actif" name="actif" style="width: 1rem; height: 1rem;">
                        <span>Article actif</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeArticleModal()" class="btn-secondary"
                        style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openArticleModal(article) {
            const modal = document.getElementById('articleModal');
            const form = document.getElementById('editArticleForm');

            document.getElementById('edit_compte_id').value = article.compte_id;
            document.getElementById('edit_designation').value = article.designation;
            document.getElementById('edit_actif').checked = article.actif;

            let updateUrl = "{{ route('articles.update', ':id') }}";
            form.action = updateUrl.replace(':id', article.id);
            modal.style.display = 'flex';
        }

        function closeArticleModal() {
            document.getElementById('articleModal').style.display = 'none';
        }

        document.getElementById('articleModal').addEventListener('click', function (e) {
            if (e.target === this) closeArticleModal();
        });
    </script>
@endsection
