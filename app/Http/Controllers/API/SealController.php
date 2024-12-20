<?php

namespace App\Http\Controllers\API;

use App\Models\Seal;
use App\Enums\SealState;
use App\Models\L10n;
use App\Models\Commerce;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SealController extends Controller
{
    /**
     * Display a listing of the seals.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locale = Auth::user()->language ?? 'en';

        $seals = Seal::with('commerces')->get();

        return response()->json(['data' => $seals]);
    }

    /**
     * Display the specified seal.
     *
     * @param  \App\Models\Seal  $seal
     * @return \Illuminate\Http\Response
     */
    public function show(Seal $seal)
    {
        $locale = Auth::user()->language ?? 'en';

        $translation = L10n::where('locale', $locale)
            ->where('group', 'seal')
            ->where('key', $seal->slug)
            ->first();

        $seal->translated_name = $translation->value ?? $seal->name;

        return response()->json($seal);
    }

    /**
     * Fetch commerces associated with the seal.
     *
     * @param  \App\Models\Seal  $seal
     * @return \Illuminate\Http\Response
     */
    public function commerces(Seal $seal)
    {
        $commerces = $seal->commerces()->get();

        return response()->json($commerces);
    }

    /**
     * Update the specified seal in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Seal  $seal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seal $seal)
    {
        $this->authorize('manage-seal');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:seals,slug,' . $seal->id,
            'translations' => 'nullable|array',
            'translations.*.value' => 'required|string',
        ]);

        $seal->update($data);

        if (isset($data['translations'])) {
            foreach ($data['translations'] as $locale => $translation) {
                L10n::updateOrCreate(
                    [
                        'locale' => $locale,
                        'group' => 'seal',
                        'key' => $seal->slug,
                    ],
                    [
                        'value' => $translation['value'],
                    ]
                );
            }
        }

        return response()->json($seal);
    }

    /**
     * Store a newly created seal in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-seal');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:seals,slug',
            'translations' => 'required|array',
            'translations.*.value' => 'required|string',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        $seal = Seal::create($data);

        foreach ($data['translations'] as $locale => $translation) {
            L10n::create([
                'locale' => $locale,
                'group' => 'seal',
                'key' => $seal->slug,
                'value' => $translation['value'],
            ]);
        }

        return response()->json($seal, 201);
    }

    /**
     * Remove the specified seal from storage.
     *
     * @param  \App\Models\Seal  $seal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seal $seal)
    {
        $this->authorize('manage-seal');

        
        L10n::where('group', 'seal')
            ->where('key', $seal->slug)
            ->delete();

        $seal->delete();

        return response()->json(null, 204);
    }
}

