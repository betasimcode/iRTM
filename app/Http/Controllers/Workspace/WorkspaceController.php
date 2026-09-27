<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __construct(
        protected WorkspaceService $service
    ) {
    }

    /**
     * Lista los Workspaces disponibles.
     */
    public function index(): View
    {
        return view(
            'workspace.index',
            [
                'current' => $this->service->current(
                    auth()->user()
                ),

                'workspaces' => $this->service->available(
                    auth()->user()
                )
            ]
        );
    }

    /**
     * Cambia el Workspace activo.
     */
    public function activate(
        int $workspace
    ): RedirectResponse
    {
        $this->service->activate(
            auth()->user(),
            $workspace
        );

        return back()->with(
            'success',
            'Workspace actualizado.'
        );
    }
}
