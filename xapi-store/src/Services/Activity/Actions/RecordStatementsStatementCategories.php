<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Relations\StatementActivity;

trait RecordStatementsStatementCategories
{
    /**
     * Statement categories repository.
     *
     * @var \Trax\XapiStore\Stores\StatementCategories\StatementCategoryRepository
     */
    protected $statementCategories;

    /**
     * Save the statements statement categories.
     *
     * @param  array  $activitiesInfo
     * @return void
     */
    protected function recordStatementsStatementCategories(array $activitiesInfo)
    {
        if (!config('trax-xapi-store.requests.relational', false)
            || !config('trax-xapi-store.processing.record_statement_categories', false)) {
            return;
        }

        $this->statementCategories = app(\Trax\XapiStore\Stores\StatementCategories\StatementCategoryRepository::class);

        // Collect info.
        $categoriesInfo = $this->statementsStatementCategoriesInfo($activitiesInfo);

        // Get existing records.
        $existingCategories = $this->getExistingStatementCategories($categoriesInfo);

        // Insert the new verbs.
        $newCategoriesInfo = $this->getNewStatementCategoriesInfo($existingCategories, $categoriesInfo);
        try {
            $this->insertStatementCategories($newCategoriesInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue when queues are not used.
            // We accept to loose some data here when 2 processes try to create the same verb.
        }
    }

    /**
     * Extract statement categories from a collection of activities.
     *
     * @param  array  $activitiesInfo
     * @return array
     */
    protected function statementsStatementCategoriesInfo(array $activitiesInfo): array
    {
        $infos = [];
        foreach ($activitiesInfo as $activityInfo) {
            if ($activityInfo->type != StatementActivity::TYPE_CONTEXT_CATEGORY) {
                continue;
            }

            $markedAsProfile = isset($activityInfo->activity->definition)
                && isset($activityInfo->activity->definition->type)
                && $activityInfo->activity->definition->type == 'http://adlnet.gov/expapi/activities/profile';

            $infos[] = (object)[
                'iri' => $activityInfo->iri,
                'statementId' => $activityInfo->statementId,
                'profile' => $markedAsProfile
            ];
        }
        return $infos;
    }

    /**
     * Get existing statement categories.
     *
     * @param  array  $categoriesInfo
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingStatementCategories(array $categoriesInfo): Collection
    {
        $iris = collect($categoriesInfo)->pluck('iri')->unique()->toArray();
        return $this->statementCategories->whereIriIn($iris);
    }

    /**
     * Get the new statement categories info.
     *
     * @param  \Illuminate\Support\Collection  $existingCategories
     * @param  array  $categoriesInfo
     * @return array
     */
    protected function getNewStatementCategoriesInfo(Collection $existingCategories, array $categoriesInfo): array
    {
        return array_filter($categoriesInfo, function ($profileInfo) use ($existingCategories) {
            return $existingCategories->search(function ($profile) use ($profileInfo) {
                return $profile->iri == $profileInfo->iri;
            }) === false;
        });
    }

    /**
     * Insert statement categories.
     *
     * @param  array  $categoriesInfo
     * @return void
     */
    protected function insertStatementCategories(array $categoriesInfo): void
    {
        $batch = collect($categoriesInfo)->unique('iri')->map(function ($info) {
            return [
                'iri' => $info->iri,
                'profile' => $info->profile,
                'owner_id' => TraxAuth::context('owner_id')
            ];
        })->all();

        $this->statementCategories->insert($batch);
    }
}
