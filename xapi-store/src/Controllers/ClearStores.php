<?php

namespace Trax\XapiStore\Controllers;

use Illuminate\Http\Request;
use Trax\Repo\CrudRequest;

trait ClearStores
{
    /**
     * Clear the repositories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clear(Request $request)
    {
        // clearAll can't be used because 'truncate' can't be used on tables with foreign keys.
        return $this->clearByQuery($request);
    }

    /**
     * Clear ALL the repositories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function clearAll(Request $request)
    {
        // Check permissions.
        $this->authorizer->must('xapi-extra.manage');

        // Truncate all the stores.
        $this->statements->truncate();
        $this->activities->truncate();
        $this->agents->truncate();
        $this->states->truncate();
        $this->activityProfiles->truncate();
        $this->agentProfiles->truncate();
        $this->attachments->truncate();
        $this->persons->truncate();
        $this->verbs->truncate();

        return response('', 204);
    }

    /**
     * Clear the repositories of with a given filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $ownerId
     * @return \Illuminate\Http\Response
     */
    protected function clearByQuery(Request $request)
    {
        $params = $request->validate(
            CrudRequest::validationRules()
        );
        $crudRequest = new CrudRequest($params);

        // Check permissions.
        $this->authorizer->must('xapi-extra.manage');
        
        // Delete statements.
        $scopeFilter = $this->authorizer->scopeFilter('statement');
        if (!is_null($scopeFilter)) {
            $this->statements->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete activities.
        $scopeFilter = $this->authorizer->scopeFilter('activity');
        if (!is_null($scopeFilter)) {
            $this->activities->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete agents.
        $scopeFilter = $this->authorizer->scopeFilter('agent');
        if (!is_null($scopeFilter)) {
            $this->agents->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete states.
        $scopeFilter = $this->authorizer->scopeFilter('state');
        if (!is_null($scopeFilter)) {
            $this->states->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete activity profiles.
        $scopeFilter = $this->authorizer->scopeFilter('activity_profile');
        if (!is_null($scopeFilter)) {
            $this->activityProfiles->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete agent profiles.
        $scopeFilter = $this->authorizer->scopeFilter('agent_profile');
        if (!is_null($scopeFilter)) {
            $this->agentProfiles->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete attachments.
        $scopeFilter = $this->authorizer->scopeFilter('attachment');
        if (!is_null($scopeFilter)) {
            $this->attachments->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete persons.
        $scopeFilter = $this->authorizer->scopeFilter('person');
        if (!is_null($scopeFilter)) {
            $this->persons->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        // Delete verbs.
        $scopeFilter = $this->authorizer->scopeFilter('verb');
        if (!is_null($scopeFilter)) {
            $this->verbs->addFilter($scopeFilter)->deleteByQuery($crudRequest->query());
        }

        return response('', 204);
    }
}
