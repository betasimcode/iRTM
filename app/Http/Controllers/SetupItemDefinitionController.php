<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetupitemDefinition;
use App\Models\SetupLabel;
use Illuminate\Support\Facades\DB;

class SetupItemDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $query = SetupItemDefinition::query();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(raw_key) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(label) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(zone) LIKE ?', ["%{$search}%"]);
            });
        }

        // 🎯 FILTERS
        switch ($request->filter) {

            case 'pending':
                $query->where('status', 'pending');
                break;

            case 'incomplete':
                $query->where(function ($q) {
                    $q->whereNull('label')
                    ->orWhereNull('zone');
                });
                break;

            case 'ok':
                $query->whereNotNull('label')
                    ->whereNotNull('zone');
                break;
        }

        $items = $query
            ->orderBy('raw_key')
            ->paginate(20)
            ->withQueryString();

        return view('admin.setup-items.index', compact('items'));
    }

    

    public function edit($id)
    {
        $item = SetupItemDefinition::findOrFail($id);

        $zones = SetupItemDefinition::ZONES;

        $labels = SetupLabel::orderBy('name')->pluck('name');

        return view('admin.setup-items.edit', compact('item','zones','labels'));
    }

    public function update(Request $request, $id)
    {
        $item = SetupItemDefinition::findOrFail($id);

        $item->update([
            'label' => $request->label,
            'zone' => $request->zone,
            'status' => 'mapped'
        ]);

        return redirect()
            ->route('admin.setup-items.index')
            ->with('success', 'Item actualizado');
    }

    public function sync()
    {
        // 🔥 sacar keys únicas desde setup_values
        $keys = DB::table('setup_values')
            ->select('key')
            ->distinct()
            ->pluck('key');

        $created = 0;

        foreach ($keys as $key) {

            $exists = SetupItemDefinition::where('raw_key', $key)->exists();

            if (!$exists) {
                SetupItemDefinition::create([
                    'raw_key' => $key,
                    'status' => 'pending'
                ]);
                $created++;
            }
        }

        return back()->with('success', "$created nuevos items sincronizados");
    }
}