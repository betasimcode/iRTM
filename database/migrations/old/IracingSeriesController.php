<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IracingSeries;
use Illuminate\Http\Request;

class IracingSeriesController extends Controller
{

    public function index()
    {
        $series = IracingSeries::orderBy('name')->paginate(20);

        return view('admin.iracing_series.index', compact('series'));
    }

    public function create()
    {
        return view('admin.iracing_series.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('iracing-series','public');
        }

        IracingSeries::create($data);

        return redirect()->route('iracing-series.index');
    }

    public function edit(IracingSeries $iracingSeries)
    {
        return view('admin.iracing_series.edit', compact('iracingSeries'));
    }

    public function update(Request $request, IracingSeries $iracingSeries)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('iracing-series','public');
        }

        $iracingSeries->update($data);

        return redirect()->route('iracing-series.index');
    }

    public function destroy(IracingSeries $iracingSeries)
    {
        $iracingSeries->delete();

        return back();
    }

}
