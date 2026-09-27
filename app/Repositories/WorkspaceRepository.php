<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;

class WorkspaceRepository
{
    /**
     * Obtiene un Workspace por su ID.
     */
    public function find(int $id): ?Workspace
    {
        return Workspace::query()
            ->with(['owner', 'team'])
            ->find($id);
    }

    /**
     * Obtiene un Workspace perteneciente
     * al usuario.
     */
    public function findForUser(
        User $user,
        int $workspaceId
    ): ?Workspace {

        return Workspace::query()
            ->with(['owner', 'team'])
            ->where('id', $workspaceId)
            ->where('owner_user_id', $user->id)
            ->first();
    }

    /**
     * Devuelve el Driver Workspace.
     *
     * Si no existe, lo crea automáticamente.
     */
    public function driver(User $user): ?Workspace
    {
        $workspace = Workspace::query()
            ->with(['owner'])
            ->where('owner_user_id', $user->id)
            ->where('type', 'driver')
            ->where('is_active', true)
            ->first();

        if (! $workspace) {
            $workspace = $this->createDriver($user);
        }

        return $workspace;
    }

    /**
     * Devuelve el Team Workspace.
     */
    public function team(User $user): ?Workspace
    {
        if (! $user->team_id) {
            return null;
        }

        return Workspace::query()
            ->with(['team'])
            ->where('team_id', $user->team_id)
            ->where('type', 'team')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Devuelve todos los Workspaces disponibles.
     */
    public function available(
        User $user
    ): Collection {

        return Workspace::query()
            ->with(['team'])
            ->where('owner_user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('type')
            ->get();
    }

    /**
     * Guarda el Workspace.
     */
    public function save(
        Workspace $workspace
    ): Workspace {

        $workspace->save();

        return $workspace;
    }

    public function createDriver(
        User $user
    ): Workspace {

        $workspace = new Workspace();

        $workspace->type = 'driver';

        $workspace->owner_user_id = $user->id;

        $workspace->slug = 'driver-' . $user->id;

        $workspace->is_active = true;

        $workspace->save();

        return $workspace;
    }

    public function createTeam(
        User $user
    ): Workspace {

        $workspace = new Workspace();

        $workspace->type = 'team';

        $workspace->owner_user_id = $user->id;

        $workspace->team_id = $user->team_id;

        $workspace->slug = 'team-' . $user->team_id;

        $workspace->is_active = true;

        $workspace->save();

        return $workspace;
    }

}
