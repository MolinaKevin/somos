<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los clientes
        $users = User::all();

        // Retornar la vista con los clientes
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.clients.create'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Crear el cliente después de la validación exitosa
        User::create($validatedData);

        return redirect('/admin/clients')->with('status', 'Client created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $client = User::findOrFail($id);

        return view('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = User::findOrFail($id);

        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $client)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $client->id,
        ]);

        $client->update($validatedData);

        return redirect()->route('admin.clients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $client)
    {
        $client->delete();

        return redirect('/admin/clients')->with('status', 'Client deleted successfully');
    }

    
}
