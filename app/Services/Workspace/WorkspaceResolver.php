<?php

namespace App\Services\Workspace;

use App\Models\User;
use App\Models\Workspace;
use App\Repositories\WorkspaceRepository;
use Illuminate\Support\Facades\Session;

class WorkspaceResolver
{
    public function __construct(
        protected WorkspaceRepository $repository
    ) {
    }

    /**
     * Devuelve el Workspace activo.
     */
    public function current(User $user): Workspace
    {
        $workspaceId = Session::get('workspace_id');

        if ($workspaceId) {

            $workspace = $this->repository->findForUser(
                $user,
                (int) $workspaceId
            );

            if ($workspace) {
                return $workspace;
            }

            $this->clear();
        }

        return $this->driver($user);
    }

    /**
     * Devuelve el Driver Workspace.
     * Si no existe, lo crea automáticamente.
     */
    public function driver(User $user): Workspace
    {
        return $this->repository->driver($user);
    }

    /**
     * Devuelve el Team Workspace.
     */
    public function team(User $user): ?WorkspaceContract
    {
        if (! $user->team_id) {
            return null;
        }

        $workspace = $this->repository->team($user);

        if (! $workspace) {

            $workspace = $this->repository->createTeam($user);
        }

        return new WorkspaceContext($workspace);
    }




    /**
     * Devuelve todos los Workspaces disponibles.
     */
    public function available(User $user)
    {
        return $this->repository->available($user);
    }

    /**
     * Activa un Workspace.
     */
    public function activate(
        User $user,
        int $workspaceId
    ): WorkspaceContract {

        $workspace = $this->repository
            ->findForUser(
                $user,
                $workspaceId
            );

        abort_if(
            !$workspace,
            403,
            'Workspace no disponible.'
        );

        Session::put(
            'workspace_id',
            $workspace->id
        );

        return new WorkspaceContext($workspace);
    }

    /**
     * Limpia el Workspace activo.
     */
    public function clear(): void
    {
        Session::forget(
            'workspace_id'
        );
    }
}
