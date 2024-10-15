<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commerce;

class CommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los comercios
        $commerces = Commerce::all();

        // Retornar la vista con los comercios
        return view('admin.commerces.index', compact('commerces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.commerces.create'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:commerces',
            'phone_number' => 'nullable|string|max:15',
            'city' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'points' => 'nullable|integer',
            'active' => 'boolean',
            'accepted' => 'boolean',
        ]);

        // Crear el comercio después de la validación exitosa
        Commerce::create($validatedData);

        return redirect('/admin/commerces')->with('status', 'Commerce created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $commerce = Commerce::findOrFail($id);

        return view('admin.commerces.show', compact('commerce'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $commerce = Commerce::findOrFail($id);

        return view('admin.commerces.edit', compact('commerce'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Commerce $commerce)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:commerces',
            'phone_number' => 'nullable|string|max:15',
            'city' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'points' => 'nullable|integer',
            'active' => 'boolean',
            'accepted' => 'boolean',
        ]);

        $commerce->update($validatedData);

        return redirect()->route('admin.commerces.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Commerce $commerce)
    {
        $commerce->delete();

        return redirect('/admin/commerces')->with('status', 'Commerce deleted successfully');
    }
}

