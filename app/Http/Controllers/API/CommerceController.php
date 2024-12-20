<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Commerce;
use App\Models\User;
use App\Enums\SealState;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommerceController extends Controller
{

    public function index()
    {
        $commerces = Commerce::all();
        return response()->json(['data' => $commerces]);
    }

    public function assignCategories(Request $request, Commerce $commerce) {
        $categoryIds = $request->input('categories');

        
        $categories = Category::findMany($categoryIds);
        if (count($categories) !== count($categoryIds)) {
            return response()->json(['error' => 'Some categories do not exist'], 404);
        }

        
        $commerce->categories()->sync($categoryIds);

        return response()->json(['message' => 'Categories assigned successfully'], 200);
    }

    public function associateUser(Request $request, Commerce $commerce)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        
        $commerce->users()->attach($user->id);

        return response()->json(['message' => 'User associated with commerce successfully.'], 200);
    }

    public function activate(Commerce $commerce)
    {
        $commerce->update(['active' => true]);
        return response()->json(['success' => true, 'message' => 'Commerce activated successfully.']);
    }

    public function accept(Commerce $commerce)
    {
        $commerce->update(['accepted' => true]);
        return response()->json(['success' => true, 'message' => 'Commerce accepted successfully.']);
    }

    public function deactivate(Commerce $commerce)
    {
        $commerce->update(['active' => false]);
        return response()->json(['success' => true, 'message' => 'Commerce deactivated successfully.']);
    }

    public function unaccept(Commerce $commerce)
    {
        $commerce->update([
            'accepted' => false,
            'active' => false, 
        ]);

        return response()->json(['success' => true, 'message' => 'Commerce unaccepted and deactivated successfully.']);
    }

    public function filterByCategories(Request $request)
    {
        $categoryIds = $request->input('category_ids', []);

        
        $allCategoryIds = Category::whereIn('id', $categoryIds)
            ->orWhereIn('parent_id', $categoryIds)
            ->pluck('id');

        
        $commerces = Commerce::whereHas('categories', function ($query) use ($allCategoryIds) {
            $query->whereIn('categories.id', $allCategoryIds); 
        })->get();

        return response()->json(['data' => $commerces]);
    }

    public function filterByFilters(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'seals' => ['array'],
            'seals.*.id' => ['required', 'integer', 'exists:seals,id'],
            'seals.*.state' => ['required', 'regex:/^\d+$|^(none|partial|full)$/'],
        ]);

        $normalizedSeals = SealState::normalize($validated['seals'] ?? []);

        $commerces = Commerce::filterBy(
            $validated['category_ids'] ?? [],
            $normalizedSeals
        )->get();

        return response()->json(['data' => $commerces]);
    }
    
}
