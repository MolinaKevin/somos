<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seal;
use App\Models\Commerce;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SealController extends Controller
{
    /**
     * Display a listing of the seals.
     */
    public function index()
    {
        $seals = Seal::all();
        return view('admin.seals.index', compact('seals'));
    }

    /**
     * Store a newly created seal in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'slug'  => 'nullable|string|max:255|unique:seals,slug',
            'image' => 'nullable|image|max:2048',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $seal = new Seal($validated);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('seals', 'public');
            $seal->image = $imagePath;
        }

        $seal->save();

        return redirect()->route('admin.seals.index')->with('success', 'Seal created successfully.');
    }

    /**
     * Update the specified seal in storage.
     */
    public function update(Request $request, Seal $seal)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'slug'  => 'nullable|string|max:255|unique:seals,slug,' . $seal->id,
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = $seal->slug; 
        }

        $seal->update($validated);

        return redirect()->route('admin.seals.index')->with('success', 'Seal updated successfully.');
    }


    public function updateStateImage(Request $request, Seal $seal, string $state)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        
        if (!in_array($state, ['full', 'partial', 'none'])) {
            $state = 'none';
        }

        
        $folder = "seals/{$seal->id}";

        
        Storage::disk('public')->delete("{$folder}/{$state}.svg");

        
        $imagePath = $request->file('image')->storeAs($folder, "{$state}.svg", 'public');

        return response()->json([
            'success' => true,
            'message' => "Image for state '{$state}' updated successfully.",
            'path' => $imagePath,
        ]);
    }


    /**
     * Remove the specified seal from storage.
     */
    public function destroy(Seal $seal)
    {
        
        if ($seal->image && $seal->image !== 'storage/seals/default.svg') {
            Storage::disk('public')->delete($seal->image);
        }

        $seal->delete();

        return redirect()->route('admin.seals.index')->with('success', 'Seal deleted successfully.');
    }

    /**
     * Associate commerces with the seal.
     */
    public function associateCommerces(Request $request, Seal $seal)
    {
        $validated = $request->validate([
            'commerce_ids'   => 'required|array',
            'commerce_ids.*' => 'exists:commerces,id',
        ]);

        $seal->commerces()->syncWithoutDetaching($validated['commerce_ids']);

        return redirect()->route('admin.seals.show', $seal->id)->with('success', 'Commerces associated successfully.');
    }

    /**
     * Display the specified seal.
     */
    public function show(Seal $seal)
    {
        return view('admin.seals.show', compact('seal'));
    }

    public function commerces(Seal $seal)
    {
        $commerces = $seal->commerces()->get();

        
        $availableCommerces = Commerce::whereNotIn('id', $commerces->pluck('id'))->get();

        return view('admin.seals.commerces', compact('commerces', 'seal', 'availableCommerces'));
    }

}

