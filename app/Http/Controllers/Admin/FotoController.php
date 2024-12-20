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
        
        $fotos = Foto::with(['fotable'])->get(); 

        $commerces = Commerce::getCommercesWithPhotos();
        $nros = Nro::getNrosWithPhotos();

        
        return view('admin.fotos.index', compact('fotos', 'commerces', 'nros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $commerces = Commerce::all();

        return view('admin.fotos.create', compact('commerces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'fotable_id' => 'required|exists:commerces,id', 
            'fotable_type' => 'required|string',
            'path' => 'required|string',
        ]);

        
        Foto::create($validatedData);

        return redirect('/admin/fotos')->with('status', 'Foto creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
        $foto = Foto::with(['fotable'])->findOrFail($id);

        
        return view('admin.fotos.show', compact('foto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
        $foto = Foto::findOrFail($id);
        $commerces = Commerce::all(); 

        return view('admin.fotos.edit', compact('foto', 'commerces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Foto $foto)
    {
        
        $validatedData = $request->validate([
            'fotable_id' => 'required|exists:commerces,id', 
            'fotable_type' => 'required|string',
            'path' => 'required|string',
        ]);

        
        $foto->update($validatedData);

        return redirect()->route('admin.fotos.index')->with('status', 'Foto actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Foto $foto)
    {
        
        $foto->delete();

        return redirect('/admin/fotos')->with('status', 'Foto eliminada exitosamente');
    }
}

