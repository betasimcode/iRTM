<?php

namespace App\Services\Workspace;

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;

interface WorkspaceContract
{
    public function type(): string;

    public function user(): User;

    public function team(): ?Team;

    public function isDriver(): bool;

    public function isTeam(): bool;

    public function series();

    public function cars();

    public function drivers();

    public function workspace(): Workspace;

}
