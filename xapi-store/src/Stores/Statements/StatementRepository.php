<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Repo\CrudRepository;

class StatementRepository extends CrudRepository
{
    use XapiStatementRepository, StatementFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Statements\StatementFactory
     */
    public function factory()
    {
        return StatementFactory::class;
    }
}
