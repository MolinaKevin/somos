<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointsPurchase;
use App\Models\Commerce;
use App\Models\User;

class PointsPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las compras de puntos con sus relaciones
        $pointsPurchases = PointsPurchase::with(['commerce', 'user'])->get();

        // Retornar la vista con las compras de puntos
        return view('admin.pointsPurchases.index', compact('pointsPurchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener las entidades relacionadas para el formulario
        $commerces = Commerce::all();
        $users = User::all(); // Obtener todos los usuarios

        return view('admin.pointsPurchases.create', compact('commerces', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de la compra
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'user_id' => 'required|exists:users,id',
            'points' => 'required|numeric|min:0',
        ]);

        // Crear la compra de puntos después de la validación exitosa
        PointsPurchase::create($validatedData);

        return redirect('/admin/pointsPurchases')->with('status', 'Compra de puntos creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar la compra de puntos con sus relaciones
        $pointsPurchase = PointsPurchase::with(['commerce', 'user'])->findOrFail($id);

        // Retornar la vista con los detalles de la compra de puntos
        return view('admin.pointsPurchases.show', compact('pointsPurchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar la compra de puntos y cargar las entidades relacionadas para el formulario de edición
        $pointsPurchase = PointsPurchase::findOrFail($id);
        $commerces = Commerce::all();
        $users = User::all(); // Obtener todos los usuarios

        return view('admin.pointsPurchases.edit', compact('pointsPurchase', 'commerces', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PointsPurchase $pointsPurchase)
    {
        // Validar los datos de la compra
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'user_id' => 'required|exists:users,id',
            'points' => 'required|numeric|min:0',
        ]);

        // Actualizar la compra de puntos
        $pointsPurchase->update($validatedData);

        return redirect()->route('admin.pointsPurchases.index')->with('status', 'Compra de puntos actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PointsPurchase $pointsPurchase)
    {
        // Eliminar la compra de puntos
        $pointsPurchase->delete();

        return redirect('/admin/pointsPurchases')->with('status', 'Compra de puntos eliminada exitosamente');
    }
}

