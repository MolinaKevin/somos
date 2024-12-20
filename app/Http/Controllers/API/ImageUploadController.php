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
        
        $request->validate([
            'foto' => 'required|image|max:2048', 
        ]);

        if ($request->hasFile('foto')) {
            
            $path = $request->file('foto')->storeAs(
                "fotos/commerces/{$commerceId}",
                $request->file('foto')->hashName(),
                'public'
            );

            
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
        
        $request->validate([
            'foto' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            
            $path = $request->file('foto')->storeAs(
                "fotos/nros/{$nroId}",
                $request->file('foto')->hashName(),
                'public'
            );

            
            $nro = Nro::findOrFail($nroId);
            $foto = new Foto();
            $foto->path = $path;
            $nro->fotos()->save($foto);

            return response()->json(['url' => Storage::url($path)], 200);
        }

        return response()->json(['message' => 'Error uploading image'], 500);
    }
}

