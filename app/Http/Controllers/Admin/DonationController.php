<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donation;
use App\Models\Commerce;
use App\Models\Nro;

class DonationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las donaciones
        $donations = Donation::with(['commerce', 'nro'])->get();

        // Retornar la vista con las donaciones
        return view('admin.donations.index', compact('donations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener los comercios y NROs para el formulario de creación
        $commerces = Commerce::all();
        $nros = Nro::all();

        return view('admin.donations.create', compact('commerces', 'nros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de la donación
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
            'donated_points' => 'required|numeric|min:0',
            'is_paid' => 'boolean',
        ]);

        // Crear la donación después de la validación exitosa
        Donation::create($validatedData);

        return redirect('/admin/donations')->with('status', 'Donation created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar la donación con sus relaciones
        $donation = Donation::with(['commerce', 'nro'])->findOrFail($id);

        // Retornar la vista con los detalles de la donación
        return view('admin.donations.show', compact('donation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar la donación y cargar comercios y NROs para el formulario de edición
        $donation = Donation::findOrFail($id);
        $commerces = Commerce::all();
        $nros = Nro::all();

        return view('admin.donations.edit', compact('donation', 'commerces', 'nros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donation $donation)
    {
        // Validar los datos de la donación
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
            'donated_points' => 'required|numeric|min:0',
            'is_paid' => 'boolean',
        ]);

        // Actualizar la donación
        $donation->update($validatedData);

        return redirect()->route('admin.donations.index')->with('status', 'Donation updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Donation $donation)
    {
        // Eliminar la donación
        $donation->delete();

        return redirect('/admin/donations')->with('status', 'Donation deleted successfully');
    }
}

