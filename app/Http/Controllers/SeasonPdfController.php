<?php

namespace App\Http\Controllers;

use App\Models\SeasonPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SeasonPdfController extends Controller
{
    public function index()
    {
        $pdfs = SeasonPdf::query()
            ->orderByDesc('file')
            ->get();

        return view(
            'admin.season-pdfs.index',
            compact('pdfs')
        );
    }

    public function create()
    {
        return view('admin.season-pdfs.create');
    }

    public function store(Request $request)
    {
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

            'file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:51200',
            ],
        ]);

        $filename =
            $data['year']
            . '_'
            . $data['season']
            . '.pdf';

        /*
         * El parser trabaja directamente sobre
         * su directorio samples.
         */
        $parserSamplesPath = base_path(
            'tools/iracing-season-parses/samples'
        );

        if (! File::isDirectory($parserSamplesPath)) {
            abort(
                500,
                'No existe el directorio samples del parser.'
            );
        }

        /*
         * No sobrescribimos un PDF existente.
         */
        if (
            SeasonPdf::where(
                'file',
                $filename
            )->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'file' =>
                        "Ya existe el PDF {$filename}.",
                ]);
        }

        // $destination =
        //     $parserSamplesPath
        //     . DIRECTORY_SEPARATOR
        //     . $filename;

        /*
         * Guardamos físicamente el archivo
         * con el nombre normalizado.
         */
        $request
            ->file('file')
            ->move(
                $parserSamplesPath,
                $filename
            );

        SeasonPdf::create([
            'file' => $filename,
        ]);

        return redirect()
            ->route('admin.season-pdfs.index')
            ->with(
                'success',
                "PDF {$filename} subido correctamente."
            );
    }
}
