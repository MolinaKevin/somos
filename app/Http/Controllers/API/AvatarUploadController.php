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
        
        $request->validate([
            'avatar' => 'required|image|max:2048', 
        ]);

        if ($request->hasFile('avatar')) {
            
            $avatar = $request->file('avatar');
            $avatarExtension = $avatar->extension();

            $path = "avatars/commerces/{$commerceId}"; 
            Storage::disk('public')->put($path, file_get_contents($avatar));

            
            $commerce = Commerce::findOrFail($commerceId);
            $commerce->avatar = $path; 
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
        
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            
            $avatar = $request->file('avatar');
            $avatarExtension = $avatar->extension();

            $path = "avatars/nros/{$nroId}"; 
            
            Storage::disk('public')->put($path, file_get_contents($avatar));

            
            $nro = Nro::findOrFail($nroId);
            $nro->avatar = $path; 
            $nro->save();

            return response()->json(['url' => Storage::url($path)], 200);
        }

        return response()->json(['message' => 'Error uploading avatar'], 500);
    }
}

