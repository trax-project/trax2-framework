<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;

trait RecordStatementsActivityTypes
{
    /**
     * Activity types repository.
     *
     * @var \Trax\XapiStore\Stores\ActivityTypes\ActivityTypeRepository
     */
    protected $activityTypes;

    /**
     * Save the statements activity types.
     *
     * @param  array  $activitiesInfo
     * @return void
     */
    protected function recordStatementsActivityTypes(array $activitiesInfo)
    {
        if (!config('trax-xapi-store.requests.relational', false)
            || !config('trax-xapi-store.processing.record_activity_types', false)) {
            return;
        }

        $this->activityTypes = app(\Trax\XapiStore\Stores\ActivityTypes\ActivityTypeRepository::class);

        // Collect info.
        $typesInfo = $this->statementsActivityTypesInfo($activitiesInfo);

        // Get existing records.
        $existingTypes = $this->getExistingActivityTypes($typesInfo);

        // Insert the new verbs.
        $newTypesInfo = $this->getNewActivityTypesInfo($existingTypes, $typesInfo);
        try {
            $this->insertActivityTypes($newTypesInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue when queues are not used.
            // We accept to loose some data here when 2 processes try to create the same verb.
        }
    }

    /**
     * Extract activity types from a collection of activities.
     *
     * @param  array  $activitiesInfo
     * @return array
     */
    protected function statementsActivityTypesInfo(array $activitiesInfo): array
    {
        $infos = [];
        foreach ($activitiesInfo as $activityInfo) {
            if (!isset($activityInfo->activity->definition)
                || !isset($activityInfo->activity->definition->type)) {
                continue;
            }
            $infos[] = (object)[
                'iri' => $activityInfo->activity->definition->type,
                'statementId' => $activityInfo->statementId,
            ];
        }
        return $infos;
    }

    /**
     * Get existing activity types.
     *
     * @param  array  $typesInfo
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingActivityTypes(array $typesInfo): Collection
    {
        $iris = collect($typesInfo)->pluck('iri')->unique()->toArray();
        return $this->activityTypes->whereIriIn($iris);
    }

    /**
     * Get the new activity types info.
     *
     * @param  \Illuminate\Support\Collection  $existingTypes
     * @param  array  $typesInfo
     * @return array
     */
    protected function getNewActivityTypesInfo(Collection $existingTypes, array $typesInfo): array
    {
        return array_filter($typesInfo, function ($typeInfo) use ($existingTypes) {
            return $existingTypes->search(function ($type) use ($typeInfo) {
                return $type->iri == $typeInfo->iri;
            }) === false;
        });
    }

    /**
     * Insert activity types.
     *
     * @param  array  $typesInfo
     * @return void
     */
    protected function insertActivityTypes(array $typesInfo): void
    {
        $batch = collect($typesInfo)->unique('iri')->map(function ($info) {
            return [
                'iri' => $info->iri,
                'owner_id' => TraxAuth::context('owner_id')
            ];
        })->all();

        $this->activityTypes->insert($batch);
    }
}
