<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Commerce;
use App\Models\Nro;

class AvatarUploadController extends Controller
{
    /**
     * Subir y asignar un avatar a un comercio.
     */
    public function uploadCommerceAvatar(Request $request, $commerceId)
    {
        // Validar que el campo 'avatar' esté presente y sea una imagen
        $request->validate([
            'avatar' => 'required|image|max:2048', // Usar 'avatar' como campo
        ]);

        if ($request->hasFile('avatar')) {
            // Generar el path donde se guardará el avatar del comercio
            $avatar = $request->file('avatar');
            $avatarExtension = $avatar->extension();

            $path = "avatars/commerces/{$commerceId}"; 
            Storage::disk('public')->put($path, file_get_contents($avatar));

            // Asociar el avatar al comercio y actualizar el campo 'avatar' en la base de datos
            $commerce = Commerce::findOrFail($commerceId);
            $commerce->avatar = $path; // Guardar el path completo
            $commerce->save();

            return response()->json(['url' => Storage::url($path)], 200);
        }

        return response()->json(['message' => 'Error uploading avatar'], 500);
    }

    /**
     * Subir y asignar un avatar a una NRO.
     */
    public function uploadNroAvatar(Request $request, $nroId)
    {
        // Validar que el campo 'avatar' esté presente y sea una imagen
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // Generar el path donde se guardará el avatar de la NRO
            $avatar = $request->file('avatar');
            $avatarExtension = $avatar->extension();

            $path = "avatars/nros/{$nroId}"; // Guardar sin extensión
            // Guardar el avatar en el disco 'public' con el path especificado
            Storage::disk('public')->put($path, file_get_contents($avatar));

            // Asociar el avatar a la NRO y actualizar el campo 'avatar' en la base de datos
            $nro = Nro::findOrFail($nroId);
            $nro->avatar = $path; // Guardar el path completo
            $nro->save();

            return response()->json(['url' => Storage::url($path)], 200);
        }

        return response()->json(['message' => 'Error uploading avatar'], 500);
    }
}

