<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Foto;
use App\Models\Commerce;
use App\Models\Nro;

class ImageUploadController extends Controller
{
    public function uploadCommerceImage(Request $request, $commerceId)
    {
        // Validar que el campo 'foto' esté presente y sea una imagen
        $request->validate([
            'foto' => 'required|image|max:2048', // Usar 'foto' como campo
        ]);

        if ($request->hasFile('foto')) {
            // Generar un path amigable para la imagen
            $path = $request->file('foto')->storeAs(
                "fotos/commerces/{$commerceId}",
                $request->file('foto')->hashName(),
                'public'
            );

            // Guardar la imagen en la base de datos y asociarla al comercio
            $commerce = Commerce::findOrFail($commerceId);
            $foto = new Foto();
            $foto->path = $path;
            $commerce->fotos()->save($foto);

            return response()->json(['url' => Storage::url($path)], 200);
        }

        return response()->json(['message' => 'Error uploading image'], 500);
    }

    public function uploadNroImage(Request $request, $nroId)
    {
        // Validar que el campo 'foto' esté presente y sea una imagen
        $request->validate([
            'foto' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            // Generar un path amigable para la imagen
            $path = $request->file('foto')->storeAs(
                "fotos/nros/{$nroId}",
                $request->file('foto')->hashName(),
                'public'
            );

            // Guardar la imagen en la base de datos y asociarla a la NRO
            $nro = Nro::findOrFail($nroId);
            $foto = new Foto();
            $foto->path = $path;
            $nro->fotos()->save($foto);

            return response()->json(['url' => Storage::url($path)], 200);
        }

        return response()->json(['message' => 'Error uploading image'], 500);
    }
}

