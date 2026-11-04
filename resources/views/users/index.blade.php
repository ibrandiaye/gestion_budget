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
                            <td style="padding: 0.75rem; white-space: nowrap;">
                                <button type="button" onclick="openUserModal({{ json_encode($user) }})" 
                                    title="Modifier l'utilisateur"
                                    style="color: var(--primary); background: none; border: none; cursor: pointer; margin-right: 0.5rem; font-size: 1.05rem;">✏️</button>

                                <button type="button" onclick="openPasswordModal({{ json_encode($user) }})" 
                                    title="Changer le mot de passe"
                                    style="color: #d97706; background: none; border: none; cursor: pointer; margin-right: 0.5rem; font-size: 1.05rem;">🔑</button>
                                
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            title="Supprimer l'utilisateur"
                                            style="color: var(--danger); background: none; border: none; cursor: pointer; font-size: 1.05rem;">🗑️</button>
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
                    <input type="password" name="password" required minlength="8"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Rôle</label>
                    <select name="role" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                        @endforeach
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

    <!-- Edit User Modal -->
    <div id="userModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 520px; margin: 2rem; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0;">Modifier Utilisateur</h3>
                <button type="button" onclick="closeUserModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT') 
                
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
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                        @endforeach
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

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Service</label>
                    <select id="edit_service_id" name="service_id"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Aucun</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Password update section in edit modal -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem; margin-bottom: 1.5rem;">
                    <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem; color: var(--text-color, #334155); display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔒</span> Modifier le mot de passe <small style="font-weight: normal; color: var(--text-muted);">(optionnel)</small>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.4rem;">Nouveau mot de passe</label>
                        <input type="password" id="edit_password" name="password" minlength="8" placeholder="Laisser vide pour ne pas modifier (min. 8 car.)" autocomplete="new-password"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.4rem;">Confirmer le mot de passe</label>
                        <input type="password" id="edit_password_confirmation" name="password_confirmation" placeholder="Confirmer le nouveau mot de passe" autocomplete="new-password"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeUserModal()" class="btn-secondary" style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dedicated Change Password Modal -->
    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 480px; margin: 2rem; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🔑</span> Changer le mot de passe
                </h3>
                <button type="button" onclick="closePasswordModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
            </div>

            <p style="margin-bottom: 1.25rem; font-size: 0.9rem; color: var(--text-muted); background: var(--slate-50, #f8fafc); padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                Utilisateur : <strong id="password_user_name" style="color: var(--text-color, #1e293b);"></strong>
            </p>

            <form id="changePasswordForm" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nouveau mot de passe</label>
                    <input type="password" name="password" required minlength="8" placeholder="Au moins 8 caractères" autocomplete="new-password"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Retapez le nouveau mot de passe" autocomplete="new-password"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closePasswordModal()" class="btn-secondary" style="flex: 1; text-align: center;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex: 1;">Mettre à jour</button>
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

            // Reset password fields
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';

            // Set Form Action
            form.action = `/users/${user.id}`;

            modal.style.display = 'flex';
        }

        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        // Close on click outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if(e.target === this) closeUserModal();
        });

        function openPasswordModal(user) {
            const modal = document.getElementById('passwordModal');
            const form = document.getElementById('changePasswordForm');
            
            document.getElementById('password_user_name').textContent = `${user.name} (${user.email})`;
            form.action = `/users/${user.id}/password`;
            form.reset();

            modal.style.display = 'flex';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        // Close on click outside
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if(e.target === this) closePasswordModal();
        });
    </script>
@endsection
