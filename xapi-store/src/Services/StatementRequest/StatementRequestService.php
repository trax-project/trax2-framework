<?php

namespace Trax\XapiStore\Services\StatementRequest;

use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Services\StatementRequest\Actions\BuildStatementsQuery;
use Trax\XapiStore\Services\StatementRequest\Actions\RevealStatements;
use Trax\XapiStore\Services\StatementRequest\Actions\BuildResponse;
use Trax\Repo\Contracts\ReadableRepositoryContract;
use Trax\Repo\Traits\ReadableRepositoryWrapper;

class StatementRequestService implements ReadableRepositoryContract
{
    use ReadableRepositoryWrapper, BuildStatementsQuery, RevealStatements, BuildResponse;

    /**
     * Create a new class instance.
     *
     * @param  \Illuminate\Container\Container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->repository = $container->make(\Trax\XapiStore\Stores\Statements\StatementRepository::class);

        // We don't need Eloquent for pure JSON queries. We skip it to improve performances.
        if (!config('trax-xapi-store.requests.relational', false)) {
            $this->repository->dontGetWithEloquent();
        }
    }

    /**
     * Get resources with the standard CRUD process.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function get(Query $query = null): Collection
    {
        // Pending must not be set. False by dedault.
        if (!$query->hasFilter('pending')) {
            $query->addFilter(['pending' => false]);
        }

        // No relation. Use the standard request.
        if (!config('trax-xapi-store.requests.relational', false)) {
            return $this->repository->get($query);
        }
        // Use relational request first..
        return $this->getRelationalFirst($query);
    }
    
    /**
     * Try to use relational requests first on standard and UI filters.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    protected function getRelationalFirst(Query $query = null): Collection
    {
        // We need both the $query filters and the filters passed with the `addFilter()` method.
        $query = isset($query) ? $query : new Query();
        $query->addFilter(
            $this->repository->removeFilters()
        );

        // Confirm that we can reveal the agent identities.
        $reveal = $this->shouldRevealAgents($query);

        // Now we try to replace the traditional JSON filters by relational filters.
        try {
            $this->buildStatementsQuery($query, $reveal);
        } catch (NotFoundHttpException $e) {
            return collect([]);
        }

        // Now, make the request.
        $statements = $this->repository->get($query);
        if ($reveal) {
            $removeNames = isset($query) && $query->hasOption('format') && $query->option('format') == 'ids';
            $statements = $this->revealStatements($statements, $removeNames);
        }
        return $statements;
    }

    /**
     * Get statements conforming with the full standard xAPI process.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function getWithStandardProcess(Query $query = null): Collection
    {
        // Sequence based request.
        // Get only unvoided statements.
        // Do not check targeted statements.

        if ($query->hasLimit() || (
            !$query->hasFilter('agent')
            && !$query->hasFilter('verb')
            && !$query->hasFilter('activity')
            && !$query->hasFilter('registration')
        )) {
            // Force the limit.
            if (!$query->hasLimit()) {
                $query->setLimit(config('trax-xapi-store.limit', 100));
            }
            // Request.
            return $this->get(
                $query->addFilter(['voided' => false])
            );
        }

        // Not sequence based. We must check the targeted statements, including voided ones.
        // This process is not perfect because it will happen under the default limit.
        // So the number of returned statements may be under the default limit,
        // which does not mean that there is no other matching statement.
        // We recommended to limit the use of StatementRefs.

        // Force the limit.
        if (!$query->hasLimit()) {
            $query->setLimit(config('trax-xapi-store.limit', 100));
        }
        // Request.
        $all = $this->get($query);
        $result = $all->where('voided', false);

        // Get targeting statements and add them to the result.
        $targeting = $all;
        while (!$targeting->isEmpty()) {
            $targeting = $this->get(new Query(['filters' => [
                'data->object->objectType' => 'StatementRef',
                'data->object->id' => ['$in' => $targeting->pluck('uuid')],
            ]]));
            $result = $result->concat($targeting);
        }
        
        // Keep unvoided and unique statements.
        return $result->where('voided', false)->unique('id');
    }

    /**
     * Should we reveal the agents?
     *
     * The default behavior is to reveal the agents identity except when:
     * - It is explicitly set in the query.
     * - There is a pseudonymized agent passed as a filter.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function shouldRevealAgents(Query $query): bool
    {
        // Always reveal when there is no pseudonymization.
        if (!config('trax-xapi-store.gdpr.pseudonymization', false)) {
            return true;
        }

        // Don't reveal when it is explicitly set in the query.
        if ($query->hasOption('reveal') && !$query->option('reveal')) {
            return false;
        }

        // Always reveal when there is no agent filter.
        if (!$query->hasFilter('agent')) {
            return true;
        }

        // Don't reveal when the agent filter is a pseudonymized agent.
        $agent = json_decode($query->filter('agent'));
        if (isset($agent->account) && $agent->account->homePage == config('trax-xapi-store.gdpr.pseudo_iri', 'http://pseudo.traxlrs.com')) {
            return false;
        }

        return true;
    }
}
