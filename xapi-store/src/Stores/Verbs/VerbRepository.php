<?php

namespace Trax\XapiStore\Stores\Verbs;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\Caching;

class VerbRepository extends CrudRepository
{
    use VerbFilters;
    
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
     * Find an existing verb given its IRI.
     *
     * @param  string  $iri
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function findByIri(string $iri, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['iri' => $iri, 'owner_id' => $ownerId])->get()->first();
    }

    /**
     * Find a collection of verbs given their IRIs.
     *
     * @param  array  $iris
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereIriIn(array $iris, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['iri' => ['$in' => $iris], 'owner_id' => $ownerId])->get();
    }

    /**
     * Find verbs given their uiCombo.
     *
     * @param  string  $uiCombo
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereUiCombo(string $uiCombo, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uiCombo' => $uiCombo, 'owner_id' => $ownerId])->get();
    }
}
