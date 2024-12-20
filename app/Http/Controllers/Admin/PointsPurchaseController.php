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
        
        $pointsPurchases = PointsPurchase::with(['commerce', 'user'])->get();

        
        return view('admin.pointsPurchases.index', compact('pointsPurchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $commerces = Commerce::all();
        $users = User::all(); 

        return view('admin.pointsPurchases.create', compact('commerces', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'user_id' => 'required|exists:users,id',
            'points' => 'required|numeric|min:0',
        ]);

        
        PointsPurchase::create($validatedData);

        return redirect('/admin/pointsPurchases')->with('status', 'Compra de puntos creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
        $pointsPurchase = PointsPurchase::with(['commerce', 'user'])->findOrFail($id);

        
        return view('admin.pointsPurchases.show', compact('pointsPurchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
        $pointsPurchase = PointsPurchase::findOrFail($id);
        $commerces = Commerce::all();
        $users = User::all(); 

        return view('admin.pointsPurchases.edit', compact('pointsPurchase', 'commerces', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PointsPurchase $pointsPurchase)
    {
        
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'user_id' => 'required|exists:users,id',
            'points' => 'required|numeric|min:0',
        ]);

        
        $pointsPurchase->update($validatedData);

        return redirect()->route('admin.pointsPurchases.index')->with('status', 'Compra de puntos actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PointsPurchase $pointsPurchase)
    {
        
        $pointsPurchase->delete();

        return redirect('/admin/pointsPurchases')->with('status', 'Compra de puntos eliminada exitosamente');
    }
}

