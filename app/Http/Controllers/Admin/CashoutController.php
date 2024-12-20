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
        
        $cashouts = Cashout::with(['commerce'])->get();

        
        return view('admin.cashouts.index', compact('cashouts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $commerces = Commerce::all();

        return view('admin.cashouts.create', compact('commerces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'points' => 'required|numeric|min:0',
        ]);

        
        Cashout::create($validatedData);

        return redirect('/admin/cashouts')->with('status', 'Cashout creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
        $cashout = Cashout::with(['commerce'])->findOrFail($id);

        
        return view('admin.cashouts.show', compact('cashout'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
        $cashout = Cashout::findOrFail($id);
        $commerces = Commerce::all();

        return view('admin.cashouts.edit', compact('cashout', 'commerces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cashout $cashout)
    {
        
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'points' => 'required|numeric|min:0',
        ]);

        
        $cashout->update($validatedData);

        return redirect()->route('admin.cashouts.index')->with('status', 'Cashout actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cashout $cashout)
    {
        
        $cashout->delete();

        return redirect('/admin/cashouts')->with('status', 'Cashout eliminado exitosamente');
    }
}

