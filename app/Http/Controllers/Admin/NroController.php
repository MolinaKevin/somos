<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nro; // Modelo para NROs

class NroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las instituciones
        $nros = Nro::all();

        // Retornar la vista con las instituciones
        return view('admin.nros.index', compact('nros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.nros.create'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nros',
            'somos_id' => 'required|exists:somos,id'
        ]);

        // Crear la institución después de la validación exitosa
        Nro::create($validatedData);

        return redirect('/admin/nros')->with('status', 'Institution created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $nro = Nro::findOrFail($id);

        return view('admin.nros.show', compact('nro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $nro = Nro::findOrFail($id);

        return view('admin.nros.edit', compact('nro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nro $nro)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nros,email,' . $nro->id,
        ]);

        $nro->update($validatedData);

        return redirect()->route('admin.nros.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nro $nro)
    {
        $nro->delete();

        return redirect('/admin/nros')->with('status', 'Institution deleted successfully');
    }
}

