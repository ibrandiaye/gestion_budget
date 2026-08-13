<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('service')->get();
        $services = Service::all();
        $roles = Role::orderBy('id')->get();
        return view('users.index', compact('users', 'services', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'service_id' => 'nullable|required_if:role,service|exists:services,id',
            'matricule' => 'nullable|string|max:50|unique:users',
            'fonction' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'service_id' => $request->service_id,
            'matricule' => $request->matricule,
            'fonction' => $request->fonction,
        ]);

        return back()->with('success', 'Utilisateur créé avec succès.');
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'service_id' => 'nullable|required_if:role,service|exists:services,id',
            'matricule' => 'nullable|string|max:50|unique:users,matricule,' . $user->id,
            'fonction' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'service_id' => $request->service_id,
            'matricule' => $request->matricule,
            'fonction' => $request->fonction,
        ]);

        return back()->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé.');
    }
}
