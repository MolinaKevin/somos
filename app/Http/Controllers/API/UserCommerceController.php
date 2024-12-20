<?php

namespace App\Http\Controllers\API;

use App\Models\Commerce;
use App\Models\Foto;
use App\Enums\SealState;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Rules\TimeFormat;

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

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'plz' => 'required|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'email' => 'nullable|string|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'points' => 'nullable|numeric',
            'percent' => 'nullable|numeric',
            'donated_points' => 'nullable|numeric',
            'gived_points' => 'nullable|numeric',
            'opening_time' => ['nullable', new TimeFormat],
            'closing_time' => ['nullable', new TimeFormat],
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'seals' => 'nullable|array',
            'seals.*.id' => 'exists:seals,id',
            'seals.*.state' => ['required', 'regex:/^\d+$|^(none|partial|full)$/'],
        ]);

        
        $normalizedSeals = SealState::normalize($validatedData['seals'] ?? []);

        $commerce = Commerce::create($validatedData);

        
        if ($request->has('categories')) {
            $commerce->categories()->sync($validatedData['categories']);
        }

        
        if (!empty($normalizedSeals)) {
            $sealData = collect($normalizedSeals)->mapWithKeys(function ($seal) {
                return [$seal['id'] => ['state' => $seal['state']]];
            })->toArray();

            $commerce->seals()->sync($sealData);
        }

        $user->commerces()->attach($commerce->id);

        return response()->json($commerce->fresh()->load(['seals', 'categories']), 201);
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

        Log::info('entro aca', ['commerce' => $commerce]);

        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'address' => 'sometimes|string|max:255',
                'city' => 'sometimes|string|max:255',
                'plz' => 'sometimes|string|max:10',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'email' => 'nullable|string|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'website' => 'nullable|string|max:255',
                'points' => 'nullable|numeric',
                'percent' => 'nullable|numeric',
                'donated_points' => 'nullable|numeric',
                'gived_points' => 'nullable|numeric',
                'opening_time' => ['nullable', new TimeFormat],
                'closing_time' => ['nullable', new TimeFormat],
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'seals' => 'nullable|array',
                'seals.*.id' => 'exists:seals,id',
                'seals.*.state' => ['required', 'regex:/^(none|partial|full|[0-2])$/'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            
            \Log::error('Validation failed', ['errors' => $e->errors()]);

            
            return response()->json(['errors' => $e->errors()], 422);
        }

        Log::info('validado', ['validatedData' => $validatedData]);
        $normalizedSeals = SealState::normalize($validatedData['seals'] ?? []);

        $commerce->update($validatedData);

        
        if ($request->has('categories')) {
            $commerce->categories()->sync($validatedData['categories']);
        }

        
        if (!empty($normalizedSeals)) {
            $sealData = collect($normalizedSeals)->mapWithKeys(function ($seal) {
                return [$seal['id'] => ['state' => $seal['state']]];
            })->toArray();

            $commerce->seals()->sync($sealData);
        }

        if ($request->has('background_image')) {
            $backgroundImageUrl = $request->input('background_image');
            $fileName = $commerce->id . '/' . basename($backgroundImageUrl);

            $foto = Foto::where('path', 'LIKE', '%' . $fileName . '%')->first();

            if ($foto) {
                $commerce->background_image_id = $foto->id;
                Log::info('Background image actualizada', ['background_image_id' => $foto->id]);
            } else {
                return response()->json(['error' => 'Imagen no encontrada'], 404);
            }
        }

        $commerce->save();

        return response()->json($commerce->fresh()->load(['seals', 'categories']));
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

