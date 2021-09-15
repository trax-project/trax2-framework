<?php

namespace Trax\XapiStore\Stores\Verbs;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\Caching;
use Trax\XapiStore\Relations\StatementVerb;
use Trax\XapiStore\Traits\IriBasedRepo;

class VerbRepository extends CrudRepository
{
    use IriBasedRepo, VerbFilters;
    
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Verbs\VerbFactory
     */
    public function factory()
    {
        return VerbFactory::class;
    }

    /**
     * Cache a collection of verbs.
     *
     * @param  \Illuminate\Support\Collection  $verbs
     * @return void
     */
    public function cache(Collection $verbs): void
    {
        Caching::cacheVerbs(
            $verbs->pluck('iri', 'id'),
            TraxAuth::context('owner_id')
        );
    }

    /**
     * Find an existing verb ID given its IRI.
     *
     * @param  string  $iri
     * @param  \Trax\Repo\Querying\Query  $query
     * @return int|false
     */
    public function idByIri(string $iri, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        return Caching::verbId($iri, function ($iri, $ownerId) {
            $verb = $this->addFilter(['iri' => $iri, 'owner_id' => $ownerId])->get()->first();
            return $verb ? $verb->id : false;
        }, $ownerId);
    }

    /**
     * Insert relations between verbs and statements.
     *
     * @param  array  $data
     * @return void
     */
    public function insertStatementsRelations(array $data): void
    {
        StatementVerb::insert($data);
    }
}
