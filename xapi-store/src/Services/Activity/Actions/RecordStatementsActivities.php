<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Stores\Activities\ActivityFactory;
use Trax\XapiStore\Relations\StatementActivity;

trait RecordStatementsActivities
{
    /**
     * Save the statements activities.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    protected function recordStatementsActivities(Collection $statements)
    {
        // Collect activities info.
        $activitiesInfo = $this->statementsActivitiesInfo($statements);

        // Get and update existing activities.
        $existingActivities = $this->getExistingActivities($activitiesInfo);
        $this->updateActivities($existingActivities, $activitiesInfo);

        // Insert the new activities.
        $newActivitiesInfo = $this->getNewActivitiesInfo($existingActivities, $activitiesInfo);
        try {
            $newActivities = $this->insertAndGetActivities($newActivitiesInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue when queues are not used.
            // We accept to loose some data here when 2 processes try to create the same activity.
            $this->recordStatementsRelations($existingActivities, $activitiesInfo);
            return;
        }

        // Record statements relations.
        $this->recordStatementsRelations($existingActivities->concat($newActivities), $activitiesInfo);
    }

    /**
     * Extract activities from a list of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return array
     */
    protected function statementsActivitiesInfo(Collection $statements): array
    {
        $activitiesInfo = [];
        foreach ($statements as $statement) {
            // Main statement.
            $activitiesInfo = array_merge(
                $activitiesInfo,
                $this->statementActivitiesInfo($statement->id, $statement->data)
            );
            // Sub-statement.
            if (isset($statement->data->object->objectType) && $statement->data->object->objectType == 'SubStatement') {
                $activitiesInfo = array_merge(
                    $activitiesInfo,
                    $this->statementActivitiesInfo($statement->id, $statement->data->object, true)
                );
            }
        }
        return $activitiesInfo;
    }

    /**
     * Extract activities from a statement.
     *
     * @param  integer  $statementId
     * @param  object  $statementData
     * @param  bool  $sub
     * @return array
     */
    protected function statementActivitiesInfo(int $statementId, object $statementData, bool $sub = false): array
    {
        $activities = [];

        // Object.
        if (!isset($statementData->object->objectType) || $statementData->object->objectType == 'Activity') {
            $activities[] = $this->objectActivity(
                $statementId,
                $statementData->object,
                $sub
            );
        }
        // Context.
        if (isset($statementData->context) && isset($statementData->context->contextActivities)) {
            // Parent.
            if (isset($statementData->context->contextActivities->parent)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->parent,
                    StatementActivity::TYPE_CONTEXT_PARENT,
                    $sub
                ));
            }
            // Grouping.
            if (isset($statementData->context->contextActivities->grouping)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->grouping,
                    StatementActivity::TYPE_CONTEXT_GROUPING,
                    $sub
                ));
            }
            // Category.
            if (isset($statementData->context->contextActivities->category)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->category,
                    StatementActivity::TYPE_CONTEXT_CATEGORY,
                    $sub
                ));
            }
            // Other.
            if (isset($statementData->context->contextActivities->other)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->other,
                    StatementActivity::TYPE_CONTEXT_OTHER,
                    $sub
                ));
            }
        }
        return $activities;
    }

    /**
     * Extract object activity.
     *
     * @param  integer  $statementId
     * @param  object  $object
     * @param  bool  $sub
     * @return object
     */
    protected function objectActivity(int $statementId, object $object, bool $sub): object
    {
        return (object)[
            'iri' => $object->id,
            'activity' => $object,
            'type' => StatementActivity::TYPE_OBJECT,
            'sub' => $sub,
            'statementId' => $statementId
        ];
    }

    /**
     * Extract activities from context.
     *
     * @param  integer  $statementId
     * @param  object|array  $contextBranch
     * @param  string  $type
     * @param  bool  $sub
     * @return array
     */
    protected function contextActivities(int $statementId, $contextBranch, string $type, bool $sub): array
    {
        $items = is_array($contextBranch) ? $contextBranch : [$contextBranch];
        return array_map(function ($activity) use ($statementId, $type, $sub) {
            return (object)[
                'iri' => $activity->id,
                'activity' => $activity,
                'type' => $type,
                'sub' => $sub,
                'statementId' => $statementId
            ];
        }, $items);
    }

    /**
     * Get existing activities.
     *
     * @param  array  $activitiesInfo
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingActivities(array $activitiesInfo): Collection
    {
        $iris = collect($activitiesInfo)->pluck('iri')->unique()->toArray();
        return $this->repository->whereIriIn($iris);
    }

    /**
     * Update existing activities.
     *
     * @param  \Illuminate\Support\Collection  $existingActivities
     * @param  array  $activitiesInfo
     * @return void
     */
    protected function updateActivities(Collection $existingActivities, array $activitiesInfo)
    {
        if (!is_null(TraxAuth::access()) && !TraxAuth::authorizer()->can('activity.write')) {
            // The 'all' & 'define' scopes give the 'activity.write' permission.
            // If there is no access, we are in testing mode or in console command.
            return;
        }
        $existingActivities->each(function ($model) use ($activitiesInfo) {
            $activitiesData = collect($activitiesInfo)
                ->where('type', StatementActivity::TYPE_OBJECT)
                ->where('iri', $model->iri)
                ->map(function ($activityInfo) {
                    return ['data' => $activityInfo->activity];
                })
                ->all();
                $this->repository->mergeModelWithMultipleData($model, $activitiesData);
        });
    }

    /**
     * Get the new activities info.
     *
     * @param  \Illuminate\Support\Collection  $existingActivities
     * @param  array  $activitiesInfo
     * @return array
     */
    protected function getNewActivitiesInfo(Collection $existingActivities, array $activitiesInfo): array
    {
        return array_filter($activitiesInfo, function ($activityInfo) use ($existingActivities) {
            return $existingActivities->search(function ($activity) use ($activityInfo) {
                return $activity->iri == $activityInfo->iri;
            }) === false;
        });
    }

    /**
     * Insert new activities.
     *
     * @param  array  $activitiesInfo
     * @return \Illuminate\Support\Collection
     */
    protected function insertAndGetActivities(array $activitiesInfo): Collection
    {
        $batch = collect($activitiesInfo)->groupBy('iri')->map(function ($activityInfos, $iri) {
            $model = (object)['data' => (object)[
                'id' => $iri
            ]];
            foreach ($activityInfos as $activityInfo) {
                if (is_null(TraxAuth::access())
                    || TraxAuth::testingAsAdmin()
                    || TraxAuth::authorizer()->can('activity.write')
                ) {
                    // The 'all' & 'define' scopes give the 'activity.write' permission.
                    // If there is no access, we are in testing mode or in console command.
                    // We may also have an admin access in testing mode.
                    // We merge all activity data before creating the activity.
                    ActivityFactory::merge($model, ['data' => $activityInfo->activity]);
                } else {
                    return [
                        'data' => ['id' => $activityInfo->iri],
                        'owner_id' => TraxAuth::context('owner_id')
                    ];
                }
            }
            return [
                'data' => $model->data,
                'owner_id' => TraxAuth::context('owner_id')
            ];
        })->values()->all();

        return $this->repository->insertAndGet($batch);
    }

    /**
     * Record statements relations.
     *
     * @param  \Illuminate\Support\Collection  $activities
     * @param  array  $activitiesInfo
     * @return void
     */
    protected function recordStatementsRelations(Collection $activities, array $activitiesInfo): void
    {
        if (!config('trax-xapi-store.requests.relational', false)) {
            return;
        }
        $relations = collect($activitiesInfo)->map(function ($info) use ($activities) {
            return [
                'activity_id' => $activities->where('iri', $info->iri)->first()->id,
                'statement_id' => $info->statementId,
                'type' => intval($info->type),
                'sub' => $info->sub,
            ];
        });
        $this->repository->insertStatementsRelations($relations->all());
    }
}
