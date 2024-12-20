<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donation;
use App\Models\Commerce;
use App\Models\Nro;

class DonationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $donations = Donation::with(['commerce', 'nro'])->get();

        
        return view('admin.donations.index', compact('donations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $commerces = Commerce::all();
        $nros = Nro::all();

        return view('admin.donations.create', compact('commerces', 'nros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
            'donated_points' => 'required|numeric|min:0',
            'is_paid' => 'boolean',
        ]);

        
        Donation::create($validatedData);

        return redirect('/admin/donations')->with('status', 'Donation created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
        $donation = Donation::with(['commerce', 'nro'])->findOrFail($id);

        
        return view('admin.donations.show', compact('donation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
        $donation = Donation::findOrFail($id);
        $commerces = Commerce::all();
        $nros = Nro::all();

        return view('admin.donations.edit', compact('donation', 'commerces', 'nros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donation $donation)
    {
        
        $validatedData = $request->validate([
            'commerce_id' => 'required|exists:commerces,id',
            'nro_id' => 'required|exists:nros,id',
            'points' => 'required|numeric|min:0',
            'donated_points' => 'required|numeric|min:0',
            'is_paid' => 'boolean',
        ]);

        
        $donation->update($validatedData);

        return redirect()->route('admin.donations.index')->with('status', 'Donation updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Donation $donation)
    {
        
        $donation->delete();

        return redirect('/admin/donations')->with('status', 'Donation deleted successfully');
    }
}

