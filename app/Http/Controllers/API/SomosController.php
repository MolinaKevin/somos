<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Somos;
use Illuminate\Http\Request;

class SomosController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15); 

        
        $somos = Somos::paginate($perPage);

        
        return response()->json($somos, 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'plz' => 'required|string|max:10',
            
        ]);

        $somos = Somos::create($validatedData);

        return response()->json($somos, 201);
    }

    public function show(Somos $somos)
    {
        return response()->json($somos);
    }

    public function update(Request $request, Somos $somos)
    {
        
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'plz' => 'sometimes|required|string|max:10',
        ]);

        
        $dataToUpdate = array_merge(
            $validatedData,
            $request->only(['description', 'email', 'phone_number', 'website', 'operating_hours'])
        );

        
        $somos->update($dataToUpdate);


        return response()->json($somos);
    }



    public function destroy(Somos $somos)
    {
        $somos->delete();

        return response()->json(null, 204);
    }
}

