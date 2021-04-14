<?php

namespace Trax\XapiStore\Traits;

trait MagicFilters
{
    /**
     * Does agent filter support relational request?
     *
     * @param  string  $field
     * @return bool
     */
    protected function relationalMagicAgent($field)
    {
        return $this->getMagicPrefixedField($field, 'account')
            || $this->getMagicAgentMbox($field);
    }

    /**
     * Does verb filter support relational request?
     *
     * @param  string  $field
     * @return bool
     */
    protected function relationalMagicVerb($field)
    {
        return true;
    }

    /**
     * Does activity filter support relational request?
     *
     * @param  string  $field
     * @return bool
     */
    protected function relationalMagicActivity($field)
    {
        return $this->getMagicPrefixedField($field, 'type')
            || $this->getMagicHttpField($field);
    }

    /**
     * Check if there is an agent filter.
     *
     * @param  string  $field
     * @return array
     */
    protected function hasMagicAgentFilter($field)
    {
        // Account filter.
        if ($account = $this->getMagicPrefixedField($field, 'account')) {
            return true;
        }

        // Mbox filter.
        if ($mbox = $this->getMagicAgentMbox($field)) {
            return true;
        }

        // Fulltext search on name has been removed because it can't be used
        // on pseudonymized agents with the reveal option.
        return false;
    }

    /**
     * Get agent filter.
     *
     * @param  string  $field
     * @param  string  $target
     * @return array
     */
    protected function getMagicAgentFilter($field, string $target = 'data')
    {
        // Account filter.
        if ($account = $this->getMagicPrefixedField($field, 'account')) {
            $parts = explode('@', $account);
            $filter = [
                [$target.'->account->name' => $parts[0]],
            ];
            if (count($parts) > 1) {
                $filter[] = [$target.'->account->homePage' => $parts[1]];
            }
            return $filter;
        }

        // Mbox filter.
        if ($mbox = $this->getMagicAgentMbox($field)) {
            return [
                [$target.'->mbox' => 'mailto:' . $mbox],
            ];
        }

        // Fulltext search on name has been removed because it can't be used
        // on pseudonymized agents with the reveal option.
        return [
            [$target.'->mbox' => 'mailto:no-one'],
        ];
    }

    /**
     * Get verb filter.
     *
     * @param  string  $field
     * @param  string  $target
     * @return array
     */
    protected function getMagicVerbFilter($field, string $target = null)
    {
        $target = isset($target) ? $target.'->id' : 'iri';
        return $this->getMagicIriFilter($field, $target);
    }

    /**
     * Get activity filter.
     *
     * @param  string  $field
     * @param  string  $target
     * @return array
     */
    protected function getMagicActivityFilter($field, string $target = null)
    {
        // Fulltext search on name.
        if ($name = $this->getMagicPrefixedField($field, 'name')) {
            $target = isset($target) ? $target : 'data';
            return [
                [$target.'->definition->name' => ['$text' => $name]],
            ];
        }

        // Fulltext search on type.
        if ($type = $this->getMagicPrefixedField($field, 'type')) {
            $target = isset($target) ? $target : 'data';
            return $this->getMagicIriFilter($type, $target.'->definition->type');
        }

        // Magic search on ID.
        $target = isset($target) ? $target.'->id' : 'iri';
        return $this->getMagicIriFilter($field, $target);
    }

    /**
     * Get IRI filter.
     *
     * @param  string  $field
     * @param  string  $target
     * @return array
     */
    protected function getMagicIriFilter($field, string $target)
    {
        // Exact match.
        if ($iri = $this->getMagicHttpField($field)) {
            return [
                [$target => $iri],
            ];
        }

        // Fulltext search on ID.
        return [
            [$target => ['$text' => $field]],
        ];
    }

    /**
     * Get magic activity name.
     *
     * @param  string  $field
     * @param  string  $prefix
     * @return string|false
     */
    protected function getMagicPrefixedField($field, $prefix)
    {
        if (\Str::startsWith($field, $prefix.':')) {
            $field = \Str::after($field, $prefix.':');
            if (!empty($field)) {
                return $field;
            }
        }
        return false;
    }

    /**
     * Get magic agent mbox.
     *
     * @param  string  $field
     * @return string|false
     */
    protected function getMagicAgentMbox($field)
    {
        $parts = explode('@', $field);
        if (count($parts) > 1) {
            return $field;
        }
        return false;
    }

    /**
     * Get magic activity id.
     *
     * @param  string  $field
     * @return string|false
     */
    protected function getMagicHttpField($field)
    {
        if (\Str::startsWith($field, 'http')) {
            return $field;
        }
        return false;
    }
}
