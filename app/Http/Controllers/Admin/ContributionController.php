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
        
        $contributions = Contribution::with(['somos', 'nro'])->get();

        
        return view('admin.contributions.index', compact('contributions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $somes = Somos::all();
        $nros = Nro::all();

        return view('admin.contributions.create', compact('somes', 'nros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'somos_id' => 'required|exists:somos,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
        ]);

        
        Contribution::create($validatedData);

        return redirect('/admin/contributions')->with('status', 'Contribución creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
        $contribution = Contribution::with(['somos', 'nro'])->findOrFail($id);

        
        return view('admin.contributions.show', compact('contribution'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
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
        
        $validatedData = $request->validate([
            'somos_id' => 'required|exists:somos,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
        ]);

        
        $contribution->update($validatedData);

        return redirect()->route('admin.contributions.index')->with('status', 'Contribución actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contribution $contribution)
    {
        
        $contribution->delete();

        return redirect('/admin/contributions')->with('status', 'Contribución eliminada exitosamente');
    }
}

