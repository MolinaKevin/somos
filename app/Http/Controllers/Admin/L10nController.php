<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\L10n;

class L10nController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las traducciones
        $translations = L10n::all();

        // Retornar la vista con las traducciones
        return view('admin.l10ns.index', compact('translations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Retornar la vista para crear una nueva traducción
        return view('admin.l10ns.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de los datos de la traducción
        $validatedData = $request->validate([
            'locale' => 'required|string|max:2',
            'group' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);


        // Verificar si ya existe una traducción con la misma clave y grupo
        $existingTranslation = L10n::where('locale', $request->locale)
            ->where('group', $request->group)
            ->where('key', $request->key)
            ->first();

        if ($existingTranslation) {
            return response()->json(['error' => 'Duplicate translation'], 422);
        }

        // Crear la nueva traducción
        L10n::create($validatedData);

        return redirect()->route('admin.l10ns.index')->with('status', 'Traducción creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar la traducción
        $translation = L10n::findOrFail($id);

        // Retornar la vista con los detalles de la traducción
        return view('admin.l10ns.show', compact('translation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Buscar la traducción para editar
        $translation = L10n::findOrFail($id);

        return view('admin.l10ns.edit', compact('translation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'locale' => 'required|string|max:2',
            'group' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        // Buscar la traducción por ID
        $translation = L10n::findOrFail($id);

        // Actualizar la traducción con los datos validados
        $translation->update($validatedData);

        // Redirigir con un mensaje de éxito
        return redirect()->route('admin.l10ns.index')->with('status', 'Traducción actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(L10n $l10n)
    {
        // Eliminar la traducción
        $l10n->delete();

        return redirect('/admin/l10ns')->with('status', 'Traducción eliminada exitosamente');
    }
}

