<?php

namespace App\Http\Controllers\Series;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesEntry\RegisterSeriesEntryRequest;
use App\Services\SeriesEntry\SeriesEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeriesEntryController extends Controller
{
    public function __construct(
        protected SeriesEntryService $service
    ) {
    }

    /**
     * Listado de inscripciones.
     */
    public function index(): View
    {
        return view(
            'series.entries.index'
        );
    }

    /**
     * Detalle de una inscripción.
     */
    public function show(int $entry): View
    {
        return view(
            'series.entries.show',
            compact('entry')
        );
    }

    /**
     * Registra una inscripción.
     */
    public function register(
        RegisterSeriesEntryRequest $request
    ): RedirectResponse {

        $this->service->register(
            $request->data()
        );

        return redirect()

            ->back()

            ->with(
                'success',
                'Inscripción realizada correctamente.'
            );

    }

    /**
     * Abandona una competición.
     */
    public function withdraw(
        int $entry
    ): RedirectResponse {

        //
        // Próximamente
        //

        return redirect()

            ->back();

    }
}
