<?php

namespace App\Http\Controllers;

use Spatie\Browsershot\Browsershot;
use App\Models\Setup;
use App\Services\Setup\SetupPdfService;

class SetupSheetController extends Controller
{

public function show(Setup $setup)
{
    $data = SetupPdfService::build($setup);

    return view('pdf.setup-sheet', $data);
}


public function pdf(Setup $setup)
{

    $pdf = Browsershot::url(
        route('setups.sheet', $setup)
    )
    ->setOption('waitUntil', 'domcontentloaded')
    ->format('A4')
    ->margins(0,0,0,0)
    ->scale(1)
    ->pdf();

return response($pdf)
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'inline; filename="setup-sheet.pdf"');

}








}
