<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;


class CategoryService
{
    /**
     * Create a new category.
     *
     * @param  array  $data
     * @return \App\Models\Category
     */
    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'translations' => 'required|array',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Category::create($data);
    }

    /**
     * Update an existing category.
     *
     * @param  \App\Models\Category  $category
     * @param  array  $data
     * @return \App\Models\Category
     */
    public function update(Category $category, array $data)
    {
        $validator = Validator::make($data, [
            // Aquí debes especificar las reglas de validación para actualizar una categoría.
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $category->update($data);

        return $category;
    }

    /**
     * Delete a category.
     *
     * @param  \App\Models\Category  $category
     * @param  \App\Models\User  $user
     * @return void
     * @throws \Exception
     */
    public function delete(Category $category, User $user)
    {
        // Aquí podrías tener alguna lógica para verificar que el usuario tiene permiso para eliminar la categoría.

        DB::transaction(function () use ($category) {
            // Si la categoría tiene relaciones que deben ser eliminadas, hazlo aquí.

            $category->delete();
        });
    }
}

