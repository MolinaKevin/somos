<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-category');

        $data = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);


        $category = new Category;
        $category->name = $data['name'];
        $category->slug = $data['slug'];
        $category->parent_id = $data['parent_id'];

        foreach ($data['translations'] as $locale => $translation) {
            $category->setTranslation('name', $locale, $translation['name']);
        }

        $category->save();

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('manage-category');

        $this->validate($request, [
            'name' => 'required',
        ]);

        $category->update($request->all());

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $this->authorize('manage-category');

        $category->delete();

        return response()->json(null, 204);
    }
}

