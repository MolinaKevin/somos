<?php

namespace App\Http\Controllers\API;

use App\Models\Commerce;
use App\Models\Foto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserCommerceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $commerces = $user->commerces()->get();

        return response()->json([
            'data' => $commerces
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
            'donated_points' => 'nullable|numeric',
            'gived_points' => 'nullable|numeric'
        ]);

        $commerce = Commerce::create($request->all());

        $commerce->save();

        $user->commerces()->attach($commerce->id);

        return response()->json($commerce, 201);
    } 
    
    public function show(Commerce $commerce)
    {
        $user = Auth::user();

        if (!$user->commerces->contains($commerce->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($commerce);
    }

    public function update(Request $request, $commerceId)
    {
        $commerce = Commerce::findOrFail($commerceId);

        if ($request->has('background_image')) {
            $backgroundImageUrl = $request->input('background_image');

            $fileName = $commerce->id . '/' . basename($backgroundImageUrl);

            $foto = Foto::where('path','LIKE', '%'. $fileName . '%')->first();

            if ($foto) {
                $commerce->background_image_id = $foto->id;
            } else {
                return response()->json(['error' => 'Imagen no encontrada'], 404);
            }
        }

        // Guardar la actualización del comercio
        $commerce->update($request->all());
        $commerce->save();

        return response()->json($commerce);
    }


    public function destroy(Commerce $commerce)
    {
        $user = Auth::user();

        if (!$user->commerces->contains($commerce->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $commerce->delete();

        return response()->json(null, 204);
    }

}
