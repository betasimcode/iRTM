<?php

namespace App\Services\Series;

use App\Data\Series\BrowserResponseData;
use App\Models\User;
use App\Repositories\SeriesBrowserRepository;
use App\Services\Workspace\WorkspaceService;

class SeriesBrowserService
{
    public function __construct(
        protected SeriesBrowserRepository $repository,
        protected WorkspaceService $workspaceService,
    ) {
    }

    /**
     * Devuelve el estado completo del Browser de Series
     * para el Workspace activo.
     */
    public function browse(
        User $user,
        array $filters = []
    ): BrowserResponseData {

        $workspace = $this->workspaceService->current(
            $user
        );

        return $this->repository->browse(
            $workspace,
            $filters
        );

    }
}
