<?php

namespace App\Services;

use App\Models\Commerce;
use App\Models\Category;
use App\Models\User;
use App\Models\Entity;
use Illuminate\Support\Facades\DB;

class CommerceService
{

    /**
     * Create a new commerce.
     *
     * @param  array  $data
     * @param  \App\Models\User  $user
     * @return \App\Models\Commerce
     */
    public function create(array $data, User $user): Commerce
    {
        // Aquí puedes agregar lógica para verificar si el usuario puede crear un comercio.

        return DB::transaction(function () use ($data, $user) {
            $commerce = new Commerce($data);
            $commerce->user_id = $user->id; // Si el comercio debe estar asociado al usuario que lo crea.
            $commerce->save();

            // Si necesitas hacer algo más durante la creación del comercio, puedes hacerlo aquí.

            return $commerce;
        });
    }

    /**
     * Update a commerce.
     *
     * @param  \App\Models\Commerce  $commerce
     * @param  array  $data
     * @param  \App\Models\User  $user
     * @return void
     */
    public function update(Commerce $commerce, array $data, User $user)
    {
        // Aquí puedes agregar lógica para verificar si el usuario puede actualizar el comercio.

        DB::transaction(function () use ($commerce, $data) {
            $commerce->update($data);

            // Si necesitas hacer algo más durante la actualización del comercio, puedes hacerlo aquí.
        });
    }

    /**
     * Delete a commerce.
     *
     * @param  \App\Models\Commerce  $commerce
     * @param  \App\Models\User  $user
     * @return void
     */
    public function delete(Commerce $commerce, User $user)
    {
        // Aquí puedes agregar lógica para verificar si el usuario puede eliminar el comercio.

        DB::transaction(function () use ($commerce) {
            // Si el comercio tiene relaciones que debes manejar durante la eliminación, puedes hacerlo aquí.

            $commerce->delete();
        });
    }

    /**
     * Assign categories to a commerce.
     *
     * @param  \App\Models\Commerce  $commerce
     * @param  array  $categories
     * @param  \App\Models\User  $user
     * @return void
     */
    public function assignCategories(Commerce $commerce, array $categories, User $user)
    {
        // Aquí puedes agregar lógica para verificar si el usuario puede asignar categorías al comercio.

        DB::transaction(function () use ($commerce, $categories) {
            foreach ($categories as $categoryId) {
                $category = Category::find($categoryId);

                // Si la categoría no existe, puedes lanzar una excepción, saltarte este ciclo o hacer lo que necesites.
                if (!$category) continue;

                // Suponiendo que tienes una relación many-to-many entre comercios y categorías.
                $commerce->categories()->attach($category);
            }
        });
    }
}

