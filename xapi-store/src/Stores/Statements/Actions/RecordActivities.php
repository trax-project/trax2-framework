<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Stores\Activities\Activity;
use Trax\XapiStore\Stores\Activities\ActivityFactory;

trait RecordActivities
{
    /**
     * Save the statements activities.
     *
     * @param  array  $statements
     * @return void
     */
    protected function recordStatementsActivities(array $statements)
    {
        // Collect activities info.
        $activitiesInfo = $this->statementsActivitiesInfo($statements);

        // Get existing activities.
        $existingActivities = $this->getExistingActivities($activitiesInfo);

        // Update existing activities.
        $this->mergeExistingActivities($existingActivities, $activitiesInfo);

        // Insert the new activities.
        try {
            $insertedBatch = $this->insertNewActivities($existingActivities, $activitiesInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue.
            // We accept to loose some data here!
            return;
        }

        // Index activities.
        $this->indexActivities($existingActivities, $insertedBatch, $activitiesInfo);
    }

    /**
     * Get existing activities.
     *
     * @param  array  $activitiesInfo
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingActivities(array $activitiesInfo): Collection
    {
        $iris = collect($activitiesInfo)->pluck('iri')->unique();
        return $this->activities->addFilter([
            'iri' => ['$in' => $iris],
            'owner_id' => TraxAuth::context('owner_id')
        ])->get();
    }

    /**
     * Merge existing activities.
     *
     * @param  \Illuminate\Support\Collection  $existingActivities
     * @param  array  $activitiesInfo
     * @return void
     */
    protected function mergeExistingActivities(Collection $existingActivities, array $activitiesInfo)
    {
        if (!is_null(TraxAuth::access()) && !TraxAuth::authorizer()->can('activity.write')) {
            // The 'all' & 'define' scopes give the 'activity.write' permission.
            // If there is no access, we are in testing mode or in console command.
            return;
        }
        $existingActivities->each(function ($model) use ($activitiesInfo) {
            $activitiesData = collect($activitiesInfo)
                ->where('type', 'object')
                ->where('iri', $model->iri)
                ->map(function ($activityInfo) {
                    return ['data' => $activityInfo->activity];
                })
                ->all();
            $this->activities->mergeModelWithMultipleData($model, $activitiesData);
        });
    }

    /**
     * Insert new activities.
     *
     * @param  \Illuminate\Support\Collection  $existingActivities
     * @param  array  $activitiesInfo
     * @return array
     */
    protected function insertNewActivities(Collection $existingActivities, array $activitiesInfo): array
    {
        // Get the new activities.
        $newActivitiesInfo = array_filter($activitiesInfo, function ($activityInfo) use ($existingActivities) {
            return $existingActivities->search(function ($activity) use ($activityInfo) {
                return $activity->iri == $activityInfo->iri;
            }) === false;
        });

        // Prepare batch.
        $batch = collect($newActivitiesInfo)->groupBy('iri')->map(function ($activityInfos, $iri) {
            $model = (object)['data' => (object)[
                'id' => $iri
            ]];
            foreach ($activityInfos as $activityInfo) {
                if (is_null(TraxAuth::access()) || TraxAuth::authorizer()->can('activity.write')) {
                    // The 'all' & 'define' scopes give the 'activity.write' permission.
                    // If there is no access, we are in testing mode or in console command.
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

        // Insert batch.
        return $this->activities->insert($batch);
    }

    /**
     * Extract activities from a list of statements.
     *
     * @param  array  $statements
     * @return array
     */
    protected function statementsActivitiesInfo(array $statements): array
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
                    'parent',
                    $sub
                ));
            }
            // Grouping.
            if (isset($statementData->context->contextActivities->grouping)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->grouping,
                    'grouping',
                    $sub
                ));
            }
            // Category.
            if (isset($statementData->context->contextActivities->category)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->category,
                    'category',
                    $sub
                ));
            }
            // Other.
            if (isset($statementData->context->contextActivities->other)) {
                $activities = array_merge($activities, $this->contextActivities(
                    $statementId,
                    $statementData->context->contextActivities->other,
                    'other',
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
            'type' => 'object',
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
     * Index activities.
     *
     * @param  \Illuminate\Support\Collection  $existingActivities
     * @param  array  $insertedBatch
     * @param  array  $activitiesInfo
     * @return void
     */
    protected function indexActivities(Collection $existingActivities, array $insertedBatch, array $activitiesInfo): void
    {
        if (!config('trax-xapi-store.relations.statements_activities', false)) {
            return;
        }

        // Get back the new models.
        $iris = collect($insertedBatch)->pluck('iri')->toArray();
        $newActivities = $this->activities->addFilter([
            'owner_id' => TraxAuth::context('owner_id'),
            'iri' => ['$in' => $iris]
        ])->get();

        // Index them: new + existing!
        foreach ($activitiesInfo as $activityInfo) {
            if ($newActivity = $newActivities->where('iri', $activityInfo->iri)->first()) {
                $this->indexActivity($newActivity, $activityInfo);
            } else {
                $existingActivity = $existingActivities->where('iri', $activityInfo->iri)->first();
                $this->indexActivity($existingActivity, $activityInfo);
            }
        }
    }

    /**
     * Index an activity.
     *
     * @param  \Trax\XapiStore\Stores\Activities\Activity  $activity
     * @param  object  $activityInfo
     * @return void
     */
    protected function indexActivity(Activity $activity, object $activityInfo)
    {
        $activity->statements()->attach($activityInfo->statementId, [
            'type' => $activityInfo->type,
            'sub' => $activityInfo->sub,
        ]);
    }
}
