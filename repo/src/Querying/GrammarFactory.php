<?php

namespace Trax\Repo\Querying;

use InvalidArgumentException;

class GrammarFactory
{
    /**
     * Make a grammar class depending of the DB settings.
     *
     * @return \Trax\Repo\Querying\Grammar
     *
     * @throws \InvalidArgumentException
     */
    public static function make(): Grammar
    {
        $driver = config('database.default');
        switch ($driver) {
            case 'mysql':
                return new MySqlGrammar();
            case 'pgsql':
                return new PostgreSqlGrammar();
        }
        throw new InvalidArgumentException("Unsupported driver [{$driver}].");
    }
}
