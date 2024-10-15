<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Foto;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\User;

class FotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las fotos con sus relaciones
        $fotos = Foto::with(['fotable'])->get(); // Si necesitas cargar el fotable (comercio u otro)

        $commerces = Commerce::getCommercesWithPhotos();
        $nros = Nro::getNrosWithPhotos();

        // Retornar la vista con las fotos
        return view('admin.fotos.index', compact('fotos', 'commerces', 'nros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener los comercios para el formulario
        $commerces = Commerce::all();

        return view('admin.fotos.create', compact('commerces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de la foto
        $validatedData = $request->validate([
            'fotable_id' => 'required|exists:commerces,id', // Ajustar si necesitas otros fotable_type
            'fotable_type' => 'required|string',
            'path' => 'required|string',
        ]);

        // Crear la foto después de la validación exitosa
        Foto::create($validatedData);

        return redirect('/admin/fotos')->with('status', 'Foto creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar la foto con sus relaciones
        $foto = Foto::with(['fotable'])->findOrFail($id);

        // Retornar la vista con los detalles de la foto
        return view('admin.fotos.show', compact('foto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar la foto y cargar las entidades relacionadas para el formulario de edición
        $foto = Foto::findOrFail($id);
        $commerces = Commerce::all(); // Obtener los comercios

        return view('admin.fotos.edit', compact('foto', 'commerces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Foto $foto)
    {
        // Validar los datos de la foto
        $validatedData = $request->validate([
            'fotable_id' => 'required|exists:commerces,id', // Ajustar si es necesario
            'fotable_type' => 'required|string',
            'path' => 'required|string',
        ]);

        // Actualizar la foto
        $foto->update($validatedData);

        return redirect()->route('admin.fotos.index')->with('status', 'Foto actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Foto $foto)
    {
        // Eliminar la foto
        $foto->delete();

        return redirect('/admin/fotos')->with('status', 'Foto eliminada exitosamente');
    }
}

