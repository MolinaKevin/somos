<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Commerce;
use App\Models\User;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las compras con sus relaciones
        $purchases = Purchase::with(['commerce', 'user'])->get();

        // Retornar la vista con las compras
        return view('admin.purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener las entidades relacionadas para el formulario
        $commerces = Commerce::all();
        $users = User::all(); // Obtener todos los usuarios

        return view('admin.purchases.create', compact('commerces', 'users'));
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
            'amount' => 'required|numeric|min:0',
        ]);

        // Crear la compra después de la validación exitosa
        Purchase::create($validatedData);

        return redirect('/admin/purchases')->with('status', 'Compra creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar la compra con sus relaciones
        $purchase = Purchase::with(['commerce', 'user'])->findOrFail($id);

        // Retornar la vista con los detalles de la compra
        return view('admin.purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar la compra y cargar las entidades relacionadas para el formulario de edición
        $purchase = Purchase::findOrFail($id);
        $commerces = Commerce::all();
        $users = User::all(); // Obtener todos los usuarios

        return view('admin.purchases.edit', compact('purchase', 'commerces', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        // Validar los datos de la compra
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);

        // Actualizar la compra
        $purchase->update($validatedData);

        return redirect()->route('admin.purchases.index')->with('status', 'Compra actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        // Eliminar la compra
        $purchase->delete();

        return redirect('/admin/purchases')->with('status', 'Compra eliminada exitosamente');
    }
}

