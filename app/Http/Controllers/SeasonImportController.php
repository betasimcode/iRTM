<?php

namespace App\Http\Controllers;

use App\Models\IracingSerie;
use App\Models\SeasonImport;
use Illuminate\Http\Request;

class SeasonImportController extends Controller
{
    public function index()
    {
        $imports = SeasonImport::with('iracingSeries')
            ->orderByDesc('year')
            ->orderByDesc('season')
            ->orderBy('title')
            ->get();

        return view('admin.season-imports.index', compact('imports'));
    }

    public function create()
    {
        $iracingSeries = IracingSerie::query()
            ->orderBy('name')
            ->get();

        return view(
            'admin.season-imports.create',
            compact('iracingSeries')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ir_serie_id' => ['required', 'exists:iracing_series,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'season' => ['required', 'integer', 'min:1', 'max:4'],
            'title' => ['required', 'string', 'max:255'],
            'page_start' => ['required', 'integer', 'min:1'],
            'page_end' => ['required', 'integer', 'gte:page_start'],
        ]);

        SeasonImport::create($validated);

        return redirect()
            ->route('admin.season-imports.index')
            ->with('success', 'Configuración de importación creada correctamente.');
    }
}
