<?php

namespace App\Services\TeamCenter;

use App\Models\Team;
use App\Models\User;

class TeamCenterContext
{
    public function __construct(
        public readonly User $user,
        public readonly Team $team,
    ) {
    }
}
