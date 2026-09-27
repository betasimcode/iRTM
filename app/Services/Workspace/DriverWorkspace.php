<?php

namespace App\Services\Workspace;

use App\Models\Team;
use App\Models\TeamCar;
use App\Models\Series;
use App\Models\User;

class DriverWorkspace implements WorkspaceContract
{
    protected readonly User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function type(): string
    {
        return 'driver';
    }

    public function user(): User
    {
        return $this->user;
    }

    public function team(): ?Team
    {
        return null;
    }

    public function isDriver(): bool
    {
        return true;
    }

    public function isTeam(): bool
    {
        return false;
    }

    public function series()
    {
        return Series::query()
            ->whereNull('id');
    }

    public function cars()
    {
        return TeamCar::query()
            ->whereNull('id');
    }

    public function drivers()
    {
        return User::query()
            ->whereKey($this->user->id);
    }







}
