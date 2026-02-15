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
                        <th style="padding: 0.75rem;">Matricule</th>
                        <th style="padding: 0.75rem;">Fonction</th>
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
                            <td style="padding: 0.75rem; font-family: monospace;">
                                {{ $user->matricule ?? '-' }}
                            </td>
                            <td style="padding: 0.75rem;">
                                {{ $user->fonction ?? '-' }}
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
                                <button onclick="openUserModal({{ $user }})" 
                                    style="color: var(--primary); background: none; border: none; cursor: pointer; margin-right: 0.5rem;">✏️</button>
                                
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                        style="display: inline;"
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

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Matricule</label>
                    <input type="text" name="matricule"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fonction</label>
                    <input type="text" name="fonction"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
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
    </div>

    <!-- Edit Modal -->
    <div id="userModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Modifier Utilisateur</h3>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT') 
                <!-- Note: Users Controller needs update method, using POST for now if PUT not defined, 
                     but standard resource uses PUT/PATCH. Laravel accepts PUT via method spoofing. -->
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom Complet</label>
                    <input type="text" id="edit_name" name="name" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" id="edit_email" name="email" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Rôle</label>
                    <select id="edit_role" name="role" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="service">Chef de Service</option>
                        <option value="controleur">Contrôleur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Matricule</label>
                    <input type="text" id="edit_matricule" name="matricule"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fonction</label>
                    <input type="text" id="edit_fonction" name="fonction"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Service</label>
                    <select id="edit_service_id" name="service_id"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Aucun</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeUserModal()" class="btn-secondary" style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUserModal(user) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('editUserForm');
            
            // Populate fields
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_service_id').value = user.service_id || '';
            document.getElementById('edit_matricule').value = user.matricule || '';
            document.getElementById('edit_fonction').value = user.fonction || '';

            // Set Form Action (We need to add a route for update if it doesn't exist, 
            // assuming standard resource route: /users/{id})
            form.action = `/users/${user.id}`;
            
            // Laravel resource controller usually expects PUT/PATCH
            // The @method('PUT') inside form handles this.

            modal.style.display = 'flex';
        }

        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        // Close on click outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if(e.target === this) closeUserModal();
        });
    </script>
@endsection