<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contribution;
use App\Models\Somos;
use App\Models\Nro;

class ContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las contribuciones con sus relaciones
        $contributions = Contribution::with(['somos', 'nro'])->get();

        // Retornar la vista con las contribuciones
        return view('admin.contributions.index', compact('contributions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener las entidades relacionadas para el formulario
        $somes = Somos::all();
        $nros = Nro::all();

        return view('admin.contributions.create', compact('somes', 'nros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de la contribución
        $validatedData = $request->validate([
            'somos_id' => 'required|exists:somos,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
        ]);

        // Crear la contribución después de la validación exitosa
        Contribution::create($validatedData);

        return redirect('/admin/contributions')->with('status', 'Contribución creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar la contribución con sus relaciones
        $contribution = Contribution::with(['somos', 'nro'])->findOrFail($id);

        // Retornar la vista con los detalles de la contribución
        return view('admin.contributions.show', compact('contribution'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar la contribución y cargar las entidades relacionadas para el formulario de edición
        $contribution = Contribution::findOrFail($id);
        $somes = Somos::all();
        $nros = Nro::all();

        return view('admin.contributions.edit', compact('contribution', 'somes', 'nros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contribution $contribution)
    {
        // Validar los datos de la contribución
        $validatedData = $request->validate([
            'somos_id' => 'required|exists:somos,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
        ]);

        // Actualizar la contribución
        $contribution->update($validatedData);

        return redirect()->route('admin.contributions.index')->with('status', 'Contribución actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contribution $contribution)
    {
        // Eliminar la contribución
        $contribution->delete();

        return redirect('/admin/contributions')->with('status', 'Contribución eliminada exitosamente');
    }
}

