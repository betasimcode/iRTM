<?php

namespace App\Repositories;

use App\Data\SeriesEntry\RegisterSeriesEntryData;
use App\Models\SeriesEntry;
use App\Models\SeriesEntryMember;
use Illuminate\Support\Facades\DB;

class SeriesEntryRepository
{
    /**
     * Crea una inscripción completa.
     */
    public function create(
        RegisterSeriesEntryData $data
    ): SeriesEntry {

        return DB::transaction(function () use ($data) {

            $entry = SeriesEntry::create([

                'workspace_id'       => $data->workspaceId,

                'series_id'          => $data->seriesId,

                'competition_car_id' => $data->competitionCarId,

                'status'             => 'active',

                'joined_at'          => now(),

                'created_by'         => $data->createdBy,

            ]);

            foreach ($data->members as $userId) {

                SeriesEntryMember::create([

                    'series_entry_id' => $entry->id,

                    'user_id'         => $userId,

                    'role'            => 'driver',

                    'status'          => 'active',

                    'joined_at'       => now(),

                ]);

            }

            return $entry->load([
                'workspace',
                'series',
                'competitionCar',
                'members.user'
            ]);

        });
    }

    /**
     * Busca la inscripción del Workspace
     * para una Serie.
     */
    public function findByWorkspaceAndSeries(
        int $workspaceId,
        int $seriesId
    ): ?SeriesEntry {

        return SeriesEntry::query()

            ->where('workspace_id', $workspaceId)

            ->where('series_id', $seriesId)

            ->first();

    }

    /**
     * Obtiene una inscripción.
     */
    public function find(
        int $id
    ): ?SeriesEntry {

        return SeriesEntry::query()

            ->with([
                'workspace',
                'series',
                'competitionCar',
                'members.user'
            ])

            ->find($id);

    }
}
