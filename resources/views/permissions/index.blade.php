@extends('layouts.app')

@section('title', 'Gestion des Permissions')

@section('content')
    <div class="card">
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Gestion des Permissions</h3>
            <p style="color: var(--text-muted);">Configurez les permissions pour chaque rôle</p>
        </div>

        @if(session('success'))
            <div style="padding: 1rem; background: var(--success-light); color: var(--success-dark); border-radius: 0.5rem; margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            @foreach($roles as $role)
                <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 600; text-transform: capitalize;">
                        {{ $role === 'admin' ? 'Administrateur' : ($role === 'service' ? 'Chef de Service' : 'Contrôleur') }}
                    </h4>

                    <form action="{{ route('permissions.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @foreach($groupedPermissions as $module => $permissions)
                            <div style="margin-bottom: 1.5rem;">
                                <div style="background: var(--primary-50); padding: 0.5rem 0.75rem; border-radius: 0.25rem; margin-bottom: 0.5rem;">
                                    <strong style="text-transform: capitalize; font-size: 0.9rem;">{{ $module }}</strong>
                                </div>

                                @foreach($permissions as $permission)
                                    <label style="display: flex; align-items: center; padding: 0.5rem; cursor: pointer; border-radius: 0.25rem; transition: background 0.2s;"
                                           onmouseover="this.style.background='var(--slate-50)'"
                                           onmouseout="this.style.background='transparent'">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               {{ in_array($permission->id, $rolePermissions[$role]) ? 'checked' : '' }}
                                               style="margin-right: 0.5rem;">
                                        <span style="font-size: 0.85rem;">{{ $permission->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach

                        <button type="submit" class="btn-primary" style="width: 100%;">
                            Enregistrer les permissions
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endsection
