@extends('layouts.app')

@section('title', 'Gestion des Permissions')

@section('content')
    <style>
        .tab-container { display: flex; gap: 0; border-bottom: 2px solid var(--border-color); margin-bottom: 2rem; }
        .tab-btn { padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; font-weight: 500; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-btn:hover { color: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .role-card { border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; transition: box-shadow 0.2s; }
        .role-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .module-header { background: var(--primary-50, #e0f2fe); padding: 0.5rem 0.75rem; border-radius: 0.35rem; margin-bottom: 0.5rem; font-weight: 600; text-transform: capitalize; font-size: 0.9rem; }
        .perm-label { display: flex; align-items: center; padding: 0.4rem 0.5rem; cursor: pointer; border-radius: 0.25rem; transition: background 0.15s; font-size: 0.85rem; }
        .perm-label:hover { background: var(--slate-50); }
        .perm-label input { margin-right: 0.5rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
        .form-input { width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit; box-sizing: border-box; }
        .badge-role { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; font-weight: 500; background: var(--primary-50, #dbeafe); color: var(--primary, #1e40af); }
        .badge-perm { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.25rem; font-size: 0.75rem; background: var(--slate-100, #f1f5f9); color: var(--slate-600, #475569); margin: 0.15rem; }
        .select-all-btn { font-size: 0.75rem; color: var(--primary); cursor: pointer; border: none; background: none; text-decoration: underline; padding: 0; }
    </style>

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

    <div class="tab-container">
        <button class="tab-btn active" onclick="switchTab('permissions')">🔐 Permissions par Rôle</button>
        <button class="tab-btn" onclick="switchTab('roles')">👥 Gestion des Rôles</button>
        <button class="tab-btn" onclick="switchTab('actions')">⚙️ Gestion des Permissions</button>
    </div>

    {{-- ============ TAB 1: Permissions par rôle ============ --}}
    <div id="tab-permissions" class="tab-content active">
        <div class="card" style="margin-bottom: 1rem;">
            <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem;">🔐 Attribution des permissions aux rôles</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Cochez les permissions que chaque rôle doit posséder, puis enregistrez.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            @foreach($roles as $role)
                <div class="role-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h4 style="margin: 0; font-size: 1rem;">{{ $role->display_name }}</h4>
                            @if($role->description)
                                <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0.25rem 0 0;">{{ $role->description }}</p>
                            @endif
                        </div>
                        <span class="badge-role">{{ $role->name }}</span>
                    </div>

                    <form action="{{ route('permissions.update', $role->name) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @foreach($groupedPermissions as $module => $permissions)
                            <div style="margin-bottom: 1rem;">
                                <div class="module-header">
                                    {{ ucfirst(str_replace('_', ' ', $module)) }}
                                    <button type="button" class="select-all-btn" onclick="toggleAllModule(this, '{{ $role->name }}', '{{ $module }}')">tout cocher</button>
                                </div>
                                @foreach($permissions as $permission)
                                    <label class="perm-label">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               data-role="{{ $role->name }}"
                                               data-module="{{ $module }}"
                                               {{ in_array($permission->id, $rolePermissions[$role->name] ?? []) ? 'checked' : '' }}>
                                        <span>{{ $permission->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach

                        <button type="submit" class="btn-primary" style="width: 100%; margin-top: 0.5rem;">
                            💾 Enregistrer
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============ TAB 2: Gestion des rôles ============ --}}
    <div id="tab-roles" class="tab-content">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">➕ Ajouter un rôle</h3>
                <form action="{{ route('permissions.store-role') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Identifiant (slug) *</label>
                            <input type="text" name="name" required class="form-input" placeholder="Ex: chef_departement" pattern="[a-z0-9_]+" title="Lettres minuscules, chiffres et underscores uniquement">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom d'affichage *</label>
                            <input type="text" name="display_name" required class="form-input" placeholder="Ex: Chef de Département">
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Description</label>
                        <input type="text" name="description" class="form-input" placeholder="Description courte du rôle">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;">Créer le rôle</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">📋 Rôles existants</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($roles as $role)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--slate-50); border-radius: 0.5rem;">
                            <div>
                                <strong>{{ $role->display_name }}</strong>
                                <span class="badge-role" style="margin-left: 0.5rem;">{{ $role->name }}</span>
                                @if($role->description)
                                    <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0.25rem 0 0;">{{ $role->description }}</p>
                                @endif
                            </div>
                            @if($role->name !== 'admin')
                                <form action="{{ route('permissions.destroy-role', $role) }}" method="POST" onsubmit="return confirm('Supprimer ce rôle ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #fee2e2; color: #b91c1c; border: none; padding: 0.4rem 0.75rem; border-radius: 0.35rem; cursor: pointer; font-size: 0.85rem;">🗑️</button>
                                </form>
                            @else
                                <span style="font-size: 0.8rem; color: var(--text-muted);">🔒 Protégé</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ============ TAB 3: Gestion des permissions ============ --}}
    <div id="tab-actions" class="tab-content">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">➕ Ajouter une permission</h3>
                <form action="{{ route('permissions.store-permission') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Module *</label>
                            <select id="moduleSelect" class="form-input" onchange="handleDynamicSelect(this, 'moduleCustom')" style="margin-bottom: 0.5rem;">
                                <option value="">— Choisir un module —</option>
                                @foreach($groupedPermissions->keys() as $mod)
                                    <option value="{{ $mod }}">{{ ucfirst(str_replace('_', ' ', $mod)) }}</option>
                                @endforeach
                                <option value="__new__">➕ Autre (nouveau module)...</option>
                            </select>
                            <input type="text" id="moduleCustom" name="module" required class="form-input" placeholder="Nom du module" style="display: none;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Action *</label>
                            <select id="actionSelect" class="form-input" onchange="handleDynamicSelect(this, 'actionCustom')" style="margin-bottom: 0.5rem;">
                                <option value="">— Choisir une action —</option>
                                @php
                                    $allActions = $groupedPermissions->flatten()->pluck('action')->unique()->sort()->values();
                                @endphp
                                @foreach($allActions as $act)
                                    <option value="{{ $act }}">{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                                @endforeach
                                <option value="__new__">➕ Autre (nouvelle action)...</option>
                            </select>
                            <input type="text" id="actionCustom" name="action" required class="form-input" placeholder="Nom de l'action" style="display: none;">
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Nom d'affichage *</label>
                        <input type="text" name="display_name" required class="form-input" placeholder="Ex: Valider les commandes">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Description</label>
                        <input type="text" name="description" class="form-input" placeholder="Description courte">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;">Ajouter la permission</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">📋 Permissions existantes</h3>
                @foreach($groupedPermissions as $module => $permissions)
                    <div style="margin-bottom: 1.5rem;">
                        <div class="module-header">{{ ucfirst(str_replace('_', ' ', $module)) }} ({{ $permissions->count() }})</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; padding: 0.5rem 0;">
                            @foreach($permissions as $permission)
                                <div style="display: inline-flex; align-items: center; gap: 0.25rem; background: var(--slate-100); padding: 0.3rem 0.6rem; border-radius: 0.25rem; font-size: 0.8rem;">
                                    <span>{{ $permission->display_name }}</span>
                                    <form action="{{ route('permissions.destroy-permission', $permission) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette permission ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #b91c1c; cursor: pointer; font-size: 0.75rem; padding: 0 0.25rem;">✕</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function toggleAllModule(btn, roleName, moduleName) {
            const checkboxes = document.querySelectorAll(`input[data-role="${roleName}"][data-module="${moduleName}"]`);
            const allChecked = [...checkboxes].every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            btn.textContent = allChecked ? 'tout cocher' : 'tout décocher';
        }

        function handleDynamicSelect(selectEl, inputId) {
            const inputEl = document.getElementById(inputId);
            if (selectEl.value === '__new__') {
                // Show custom input, clear and focus it
                inputEl.style.display = 'block';
                inputEl.value = '';
                inputEl.required = true;
                inputEl.focus();
            } else if (selectEl.value === '') {
                // Nothing selected
                inputEl.style.display = 'none';
                inputEl.value = '';
                inputEl.required = true;
            } else {
                // Existing value selected from dropdown
                inputEl.style.display = 'none';
                inputEl.value = selectEl.value;
                inputEl.required = true;
            }
        }
    </script>
@endsection
