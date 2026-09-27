<?php

namespace App\Http\Controllers\Api\Series;

use App\Http\Controllers\Controller;
use App\Services\Series\SeriesBrowserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesBrowserController extends Controller
{
    public function __construct(
        protected SeriesBrowserService $service
    ) {
    }

    /**
     * Browser de Series.
     */
    public function __invoke()
    {
        dd('Series Browser OK');
    }




}
