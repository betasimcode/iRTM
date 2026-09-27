<?php

namespace App\Services\Workspace;

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;

class WorkspaceContext implements WorkspaceContract
{
    public function __construct(
        protected Workspace $workspace
    ) {
    }

    /**
     * Devuelve el modelo Workspace.
     */
    public function workspace(): Workspace
    {
        return $this->workspace;
    }

    /**
     * Tipo de Workspace.
     */
    public function type(): string
    {
        return $this->workspace->type;
    }

    /**
     * Usuario propietario.
     */
    public function user(): User
    {
        return $this->workspace->owner;
    }

    /**
     * Equipo asociado.
     */
    public function team(): ?Team
    {
        return $this->workspace->team;
    }

    /**
     * ¿Es un Driver Workspace?
     */
    public function isDriver(): bool
    {
        return $this->workspace->type === 'driver';
    }

    /**
     * ¿Es un Team Workspace?
     */
    public function isTeam(): bool
    {
        return $this->workspace->type === 'team';
    }

    /**
     * Inscripciones del Workspace.
     */
    public function series()
    {
        return $this->workspace->seriesEntries();
    }

    /**
     * Coches del Workspace.
     *
     * TODO
     */
    public function cars()
    {
        return collect();
    }

    /**
     * Pilotos del Workspace.
     *
     * TODO
     */
    public function drivers()
    {
        return collect();
    }
}
