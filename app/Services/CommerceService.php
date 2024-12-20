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
        

        return DB::transaction(function () use ($data, $user) {
            $commerce = new Commerce($data);
            $commerce->user_id = $user->id; 
            $commerce->save();

            

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
        

        DB::transaction(function () use ($commerce, $data) {
            $commerce->update($data);

            
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
        

        DB::transaction(function () use ($commerce) {
            

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
        

        DB::transaction(function () use ($commerce, $categories) {
            foreach ($categories as $categoryId) {
                $category = Category::find($categoryId);

                
                if (!$category) continue;

                
                $commerce->categories()->attach($category);
            }
        });
    }
}

