<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cashout;
use App\Models\Commerce;

class CashoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los cashouts con sus relaciones
        $cashouts = Cashout::with(['commerce'])->get();

        // Retornar la vista con los cashouts
        return view('admin.cashouts.index', compact('cashouts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener las entidades relacionadas para el formulario
        $commerces = Commerce::all();

        return view('admin.cashouts.create', compact('commerces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del cashout
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'points' => 'required|numeric|min:0',
        ]);

        // Crear el cashout después de la validación exitosa
        Cashout::create($validatedData);

        return redirect('/admin/cashouts')->with('status', 'Cashout creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar el cashout con sus relaciones
        $cashout = Cashout::with(['commerce'])->findOrFail($id);

        // Retornar la vista con los detalles del cashout
        return view('admin.cashouts.show', compact('cashout'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar el cashout y cargar las entidades relacionadas para el formulario de edición
        $cashout = Cashout::findOrFail($id);
        $commerces = Commerce::all();

        return view('admin.cashouts.edit', compact('cashout', 'commerces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cashout $cashout)
    {
        // Validar los datos del cashout
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'points' => 'required|numeric|min:0',
        ]);

        // Actualizar el cashout
        $cashout->update($validatedData);

        return redirect()->route('admin.cashouts.index')->with('status', 'Cashout actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cashout $cashout)
    {
        // Eliminar el cashout
        $cashout->delete();

        return redirect('/admin/cashouts')->with('status', 'Cashout eliminado exitosamente');
    }
}

