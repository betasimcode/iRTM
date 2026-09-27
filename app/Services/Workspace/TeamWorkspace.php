<?php

namespace App\Services\Workspace;

use App\Models\Team;
use App\Models\User;

class TeamWorkspace implements WorkspaceContract
{
    protected readonly User $user;

    protected readonly Team $team;

    public function __construct(User $user)
    {
        if (! $user->team) {
            throw new \LogicException('Cannot create TeamWorkspace without a team.');
        }

        $this->user = $user;
        $this->team = $user->team;
    }

    public function type(): string
    {
        return 'team';
    }

    public function user(): User
    {
        return $this->user;
    }

    public function team(): ?Team
    {
        return $this->team;
    }

    public function isDriver(): bool
    {
        return false;
    }

    public function isTeam(): bool
    {
        return true;
    }

    public function series()
    {
        return \App\Models\Series::query()
            ->where('team_id', $this->team->id);
    }

    public function cars()
    {
        return $this->team
            ->teamCars()
            ->with('car');
    }

    public function drivers()
    {
        return $this->team
            ->drivers();
    }






}
