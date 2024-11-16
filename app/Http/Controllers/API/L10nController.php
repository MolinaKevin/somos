<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\L10n;

class L10nController extends Controller
{
    public function availableLocales(): JsonResponse
    {
        $locales = L10n::distinct('locale')->pluck('locale');
        return response()->json(['locales' => $locales]);
    }
}

