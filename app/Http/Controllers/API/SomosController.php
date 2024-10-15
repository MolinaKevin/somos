<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Somos;
use Illuminate\Http\Request;

class SomosController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15); // Número de resultados por página

        // Realizar la consulta para obtener los registros de Somos
        $somos = Somos::paginate($perPage);

        // Devolver la respuesta en formato JSON con código de estado 200
        return response()->json($somos, 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'plz' => 'required|string|max:10',
            // otros campos a validar...
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
        // Validar solo los campos que deben ser requeridos o tienen restricciones específicas
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'plz' => 'sometimes|required|string|max:10',
        ]);

        // Combinar datos validados con otros datos permitidos (por ejemplo, 'description')
        $dataToUpdate = array_merge(
            $validatedData,
            $request->only(['description', 'email', 'phone_number', 'website', 'operating_hours'])
        );

        // Actualizar el modelo con todos los datos permitidos
        $somos->update($dataToUpdate);


        return response()->json($somos);
    }



    public function destroy(Somos $somos)
    {
        $somos->delete();

        return response()->json(null, 204);
    }
}

