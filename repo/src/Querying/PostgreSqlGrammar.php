<?php

namespace Trax\Repo\Querying;

use Illuminate\Database\Eloquent\Builder;

class PostgreSqlGrammar extends Grammar
{
    /**
     * Add a JSON contains condition to the query builder.
     *
     * In an array of strings: 'meta->topic->tags[*]' => 'aicc'
     * > where (meta #> '{topic,tags}')::jsonb @> '"aicc"'::jsonb
     *
     * In an array of objects: 'meta->children[*]->name' => 'child1'
     * > where (meta #> '{children}')::jsonb @> '[{"name" : "child1"}]'::jsonb
     *
     * In an array of objects: 'meta->children[*]' => ['name' => 'child1', 'age' => 10]
     * > where (meta #> '{children}')::jsonb @> '[{"name" : "child1", "age" : 10}]'::jsonb
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string  $prop
     * @param  mixed  $value
     * @param  bool  $orWhere
     * @return void
     */
    public function addJsonContainsCondition($builder, string $prop, $value, bool $orWhere)
    {
        $where = $orWhere ? 'orWhereRaw' : 'whereRaw';
        $parts = explode('[*]', $prop);

        if (empty($parts[1])) {
            if (is_array($value)) {
                // JSON value (associative array, not object).
                $candidate = '['.json_encode($value).']';
            } elseif (is_string($value)) {
                // String value.
                $candidate = '"' . $value . '"';
            } else {
                // Other scalar values (e.g. number, boolean)
                $candidate = $value;
            }
        } else {
            // We create a candidate object give the path to one of its properties.
            $candidate =  '['.$this->jsonObjectCandidate($parts[1], $value).']';
        }

        $path = $this->jsonPath($parts[0]);
        $jsonContains = "(".$path.")::jsonb @> '".$candidate."'::jsonb";
        $builder->$where($jsonContains);
    }

    /**
     * Add a JSON search condition to the query builder.
     *
     * In an array of objects: 'meta->children[*]->name' => 'child'
     * > where jsonb_path_exists(meta, '$.children[*].name ? @ like_regex "child"')
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string  $prop
     * @param  mixed  $value
     * @param  bool  $orWhere
     * @return void
     */
    public function addJsonSearchCondition($builder, string $prop, $value, bool $orWhere)
    {
        $where = $orWhere ? 'orWhereRaw' : 'whereRaw';
        list($target, $path) = $this->jsonTargetAndPath($prop);
        $jsonSearch = "jsonb_path_exists($target::jsonb, '$path ? (@ like_regex \"$value\")')";
        $builder->$where($jsonSearch);
    }

    /**
     * Get the JSON target and path to be used with JSON_CONTAINS.
     *
     * E.g. meta->topic->tags  >  meta #> '{topic,tags}'
     *
     * @param  string  $property
     * @return array
     */
    protected function jsonPath(string $property)
    {
        $names = explode('->', $property);
        $target = array_shift($names);
        $path = implode(',', $names);
        return "$target #> '{".$path."}'";

    }
}
