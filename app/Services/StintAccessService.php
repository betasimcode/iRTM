<?php

namespace App\Services;

use App\Models\Stint;
use App\Services\Workspace\WorkspaceContext;
use Illuminate\Database\Eloquent\Builder;

use App\Services\Workspace\WorkspaceContract;

class StintAccessService
{
    public function __construct(
        protected WorkspaceContract $workspace
    ) {
    }

    /**
     * Devuelve los Stints visibles para el usuario/workspace actual.
     *
     * Reglas actuales:
     *
     * Official:
     * Practice / Lone Qualify / Race
     * -> únicamente los datos del usuario propietario.
     *
     * Offline Testing:
     * public  -> visible.
     * private -> únicamente los datos del propietario.
     *
     * Datos sin clasificar:
     * -> únicamente los datos del propietario.
     */
    public function query(): Builder
    {


        $ownerUserId = $this->workspace
            ->workspace()
            ->getAttribute('owner_user_id');

        return Stint::query()
            ->where(function (Builder $query) use ($ownerUserId) {

                /*
                 * Datos oficiales.
                 */
                $query->whereIn('session_phase', [
                    'Practice',
                    'Lone Qualify',
                    'Race',
                ])
                ->where('user_id', $ownerUserId);

                /*
                 * Offline Testing.
                 */
                $query->orWhere(function (Builder $query) use ($ownerUserId) {

                    $query->where(
                        'session_phase',
                        'Offline Testing'
                    )
                    ->where(function (Builder $query) use ($ownerUserId) {

                        $query->where(
                            'visibility',
                            'public'
                        )
                        ->orWhere(function (Builder $query) use ($ownerUserId) {

                            $query->where(
                                'visibility',
                                'private'
                            )
                            ->where(
                                'user_id',
                                $ownerUserId
                            );
                        });
                    });
                });

                /*
                 * Datos históricos sin clasificación.
                 *
                 * Se mantienen accesibles únicamente
                 * para su propietario.
                 */
                $query->orWhere(function (Builder $query) use ($ownerUserId) {

                    $query->whereNull('session_phase')
                        ->where(
                            'user_id',
                            $ownerUserId
                        );
                });
            });
    }

    public function canView(Stint $stint): bool
    {
        $ownerUserId = $this->workspace
            ->workspace()
            ->getAttribute('owner_user_id');

        /*
         * Official.
         */
        if (in_array($stint->session_phase, [
            'Practice',
            'Lone Qualify',
            'Race',
        ], true)) {
            return (int) $stint->user_id === (int) $ownerUserId;
        }

        /*
         * Offline Testing.
         */
        if ($stint->session_phase === 'Offline Testing') {

            if ($stint->visibility === 'public') {
                return true;
            }

            return
                $stint->visibility === 'private'
                && (int) $stint->user_id === (int) $ownerUserId;
        }

        /*
         * Datos antiguos/no clasificados.
         */
        return (int) $stint->user_id === (int) $ownerUserId;
    }
}
