<?php

namespace App\Http\Controllers;

use App\Models\SeasonImport;
use App\Models\SeasonPdf;
use App\Services\SeasonParserService;
use Illuminate\Http\Request;
use Throwable;

class SeasonParserController extends Controller
{
    public function create()
    {
        $seasons = SeasonImport::query()
            ->select('year', 'season')
            ->distinct()
            ->orderByDesc('year')
            ->orderByDesc('season')
            ->get()
            ->map(function ($season) {
                $filename = "{$season->year}_{$season->season}.pdf";

                return [
                    'year' => $season->year,
                    'season' => $season->season,
                    'pdf' => $filename,
                    'pdf_exists' => SeasonPdf::where(
                        'file',
                        $filename
                    )->exists(),
                    'imports_count' => SeasonImport::query()
                        ->where('year', $season->year)
                        ->where('season', $season->season)
                        ->count(),
                ];
            });

        return view(
            'admin.season-parser.create',
            compact('seasons')
        );
    }

    public function run(
        Request $request,
        SeasonParserService $parser
    ) {
        $data = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
            'season' => [
                'required',
                'integer',
                'min:1',
                'max:4',
            ],
        ]);

        try {
            $result = $parser->runSeason(
                $data['year'],
                $data['season']
            );

            return view(
                'admin.season-parser.result',
                compact('result')
            );
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'parser' => $e->getMessage(),
                ]);
        }
    }
}
