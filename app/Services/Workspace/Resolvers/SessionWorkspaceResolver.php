<?php

namespace App\Services\Workspace\Resolvers;

use App\Models\User;
use App\Services\Workspace\WorkspaceContract;
use App\Services\Workspace\WorkspaceContext;
use App\Repositories\WorkspaceRepository;
use Illuminate\Support\Facades\Session;

class SessionWorkspaceResolver
{
    public function __construct(
        protected WorkspaceRepository $repository
    ) {
    }

    /**
     * Resuelve el Workspace activo utilizando la sesión.
     */
    public function resolve(
        User $user
    ): WorkspaceContract {

        $workspaceId = Session::get('workspace_id');

        if ($workspaceId) {

            $workspace = $this->repository->find($workspaceId);

            if ($workspace) {
                return new WorkspaceContext($workspace);
            }
        }

        $workspace = $this->repository->driver($user);

        if (!$workspace) {

            $workspace = $this->repository->createDriver($user);

        }

        Session::put(
            'workspace_id',
            $workspace->id
        );

        return new WorkspaceContext($workspace);
    }
}
