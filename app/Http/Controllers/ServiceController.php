<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services',
            'chef_service' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
        ]);

        Service::create($request->all());

        return back()->with('success', 'Service ajouté avec succès.');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $service->id,
            'chef_service' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
        ]);

        $service->update($request->all());

        return back()->with('success', 'Service mis à jour avec succès.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', 'Service supprimé.');
    }
}
