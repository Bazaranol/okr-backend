<?php

namespace App\Http\Controllers;

use App\Models\Skip;
use App\Models\SkipExtension;
use Illuminate\Http\Request;

class SkipExtensionController extends Controller
{
    public function index() {

    }

    public function store(Request $request, Skip $skip) {
        $request->validate([
            'new_end_date' => 'nullable|date|after_or_equal:today',
        ]);

        $extension = SkipExtension::create([
            'skip_id' => $skip->id,
            'new_end_date' => $request->new_end_date
        ]);

        return response()->json(['message' => 'Skip Extension created', 'data' => $extension]);
    }
}
