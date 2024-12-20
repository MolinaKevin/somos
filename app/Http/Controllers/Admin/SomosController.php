<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Somos;

class SomosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $somos = Somos::all();

        
        return view('admin.somos.index', compact('somos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.somos.create'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:somos',
            'city' => 'string|max:255',
            'plz' => 'string|max:255',
        ]);

        
        Somos::create($validatedData);

        return redirect('/admin/somos')->with('status', 'Somos created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $somos = Somos::findOrFail($id);

        return view('admin.somos.show', compact('somos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $somos = Somos::findOrFail($id);

        return view('admin.somos.edit', compact('somos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $somos = Somos::findOrFail($id);  

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:somos,email,' . $somos->id,
        ]);

        $somos->update($validatedData);

        return redirect()->route('admin.somos.index');
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $somos = Somos::findOrFail($id);  

        $somos->delete();

        return redirect('/admin/somos')->with('status', 'Somos deleted successfully');
    }
}

