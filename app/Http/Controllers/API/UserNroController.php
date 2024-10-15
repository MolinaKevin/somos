<?php

namespace App\Http\Controllers\API;

use App\Models\Nro;
use App\Models\Foto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserNroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $nros = $user->nros()->get();

        return response()->json([
            'data' => $nros
        ]);

    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'plz' => 'required|string|max:10',
            'email' => 'nullable|string|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'points' => 'nullable|numeric',
            'percent' => 'nullable|numeric',
            'somos_id' => 'nullable|numeric'
        ]);

        $nro = new Nro([
            'somos_id' => $request->somos_id,
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'plz' => $request->plz,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'website' => $request->website,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'points' => $request->points,
            'percent' => $request->percent,
        ]);

        $nro->save();
        $user->nros()->attach($nro->id);

        return response()->json($nro, 201);
    }

    public function show(Nro $nro)
    {
        // Solo se puede acceder si el usuario actual es el propietario de este nro
        if (!$nro->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($nro);
    }

    public function update(Request $request, $nroId)
    {
        $nro = Nro::findOrFail($nroId);
        if (!$nro->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->has('background_image')) {
            $backgroundImageUrl = $request->input('background_image');

            $fileName = $nro->id . '/' . basename($backgroundImageUrl);

            $foto = Foto::where('path','LIKE', '%'. $fileName . '%')->first();

            if ($foto) {
                $nro->background_image_id = $foto->id;
            } else {
                return response()->json(['error' => 'Imagen no encontrada'], 404);
            }
        }

        // Guardar la actualización del comercio
        $nro->update($request->all());
        $nro->save();

        return response()->json($nro);
    }

    public function destroy(Nro $nro)
    {
        if (!$nro->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $nro->delete();

        return response()->json(null, 204);
    }
}

