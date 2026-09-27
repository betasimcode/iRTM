<?php

namespace App\Services\Workspace;

use App\Models\User;
use App\Models\Workspace;

class WorkspaceService
{
    public function __construct(
        protected WorkspaceResolver $resolver
    ) {
    }

    /**
     * Devuelve el Workspace activo.
     */
    public function current(
        User $user
    ): Workspace {

        return $this->resolver->current($user);

    }

    /**
     * Cambia el Workspace activo.
     */
    public function activate(
        User $user,
        int $workspaceId
    ): Workspace {

        return $this->resolver->activate(
            $user,
            $workspaceId
        );

    }

    /**
     * Devuelve todos los Workspaces
     * disponibles para el usuario.
     */
    public function available(
        User $user
    ) {

        return $this->resolver->available(
            $user
        );

    }

    /**
     * Devuelve el Driver Workspace.
     */
    public function driver(
        User $user
    ): Workspace {

        return $this->resolver->driver(
            $user
        );

    }

    /**
     * Devuelve el Team Workspace.
     */
    public function team(
        User $user
    ): ?Workspace {

        return $this->resolver->team(
            $user
        );

    }
}
