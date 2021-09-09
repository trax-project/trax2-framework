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
    public function relationalMagicAgent($field)
    {
        return $this->getMagicPrefixedField($field, 'account')
            || $this->getMagicPrefixedField($field, 'openid')
            || $this->getMagicPrefixedField($field, 'sha1sum')
            || $this->getMagicAgentMbox($field);
    }

    /**
     * Does verb filter support relational request?
     *
     * @param  string  $field
     * @return bool
     */
    public function relationalMagicVerb($field)
    {
        return true;
    }

    /**
     * Does activity filter support relational request?
     *
     * @param  string  $field
     * @return bool
     */
    public function relationalMagicActivity($field)
    {
        return $this->getMagicPrefixedField($field, 'type')
            || $this->getMagicHttpField($field);
    }

    /**
     * Does context filter support relational request?
     *
     * @param  string  $field
     * @param  string  $target
     * @param  bool  $fulltext
     * @return bool
     */
    public function relationalMagicContext($field)
    {
        return !empty($this->getMagicContextAgentFilter($field))
            || !empty($this->getMagicContextActivityFilter($field, 'parent'))
            || !empty($this->getMagicContextActivityFilter($field, 'grouping'))
            || !empty($this->getMagicContextActivityFilter($field, 'category'))
            // No relational search by profile currently. The cost is too hight. We should index profiles.
            || empty($this->getMagicContextProfileFilter($field))
            ;
    }

    /**
     * Check if there is an agent filter.
     *
     * @param  string  $field
     * @return bool
     */
    public function hasMagicAgentFilter($field): bool
    {
        // Fulltext search on name has been removed because it can't be used
        // on pseudonymized agents with the reveal option.
        return $this->relationalMagicAgent($field);
    }

    /**
     * Check if there is an agent filter in the magic context.
     *
     * @param  string  $field
     * @return bool
     */
    public function hasMagicContextAgentFilter($field): bool
    {
        return !empty($this->getMagicContextAgentFilter($field));
    }

    /**
     * Get agent filter.
     *
     * @param  string  $field
     * @param  string  $target
     * @return array
     */
    protected function getMagicAgentFilter($field, string $target = 'vid')
    {
        // Account filter.
        if ($account = $this->getMagicPrefixedField($field, 'account')) {
            $parts = explode('@', $account);
            if (count($parts) > 1) {
                // Name + homePage.
                if ($target == 'vid') {
                    return [
                        ['vid' => 'account::' . $parts[0] . '@' . $parts[1]],
                    ];
                } else {
                    return [
                        [$target.'->account->name' => $parts[0]],
                        [$target.'->account->homePage' => $parts[1]]
                    ];
                }
            } else {
                // Name only.
                if ($target == 'vid') {
                    return [
                        ['vid' => ['$text' => 'account::' . $parts[0] . '@']],
                    ];
                } else {
                    return [
                        [$target.'->account->name' => $parts[0]],
                    ];
                }
            }
        }

        // Mbox filter.
        if ($email = $this->getMagicAgentMbox($field)) {
            if ($target == 'vid') {
                return [
                    ['vid' => 'mbox::mailto:' . $email],
                ];
            } else {
                return [
                    [$target.'->mbox' => 'mailto:' . $email],
                ];
            }
        }

        // Mbox_sha1sum filter.
        if ($sha1sum = $this->getMagicPrefixedField($field, 'sha1sum')) {
            if ($target == 'vid') {
                return [
                    ['vid' => 'mbox_sha1sum::' . $sha1sum],
                ];
            } else {
                return [
                    [$target.'->mbox_sha1sum' => $sha1sum],
                ];
            }
        }

        // Openid filter.
        if ($openid = $this->getMagicPrefixedField($field, 'openid')) {
            if ($target == 'vid') {
                return [
                    ['vid' => 'openid::' . $openid],
                ];
            } else {
                return [
                    [$target.'->openid' => $openid],
                ];
            }
        }

        return [
            [$target.'->mbox' => 'no-one'],
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
     * Get context agent filter.
     *
     * @param  string  $field
     * @return array
     */
    protected function getMagicContextAgentFilter($field)
    {
        // Account filter.
        if (\Str::startsWith($field, 'account:')) {
            $field = \Str::after($field, 'account:');
            if (empty($field)) {
                return [];
            }
            $parts = explode('@', $field);
            if (count($parts) == 1) {
                return [
                    ['$or' => [
                        'data->context->instructor->account->name' => $parts[0],
                        'data->context->team->account->name' => $parts[0],
                    ]],
                ];
            } else {
                return [
                    ['$or' => [
                        ['$and' => [
                            'data->context->instructor->account->name' => $parts[0],
                            'data->context->instructor->account->homePage' => $parts[1],
                        ]],
                        ['$and' => [
                            'data->context->team->account->name' => $parts[0],
                            'data->context->team->account->homePage' => $parts[1],
                        ]],
                    ]],
                ];
            }
        }

        // Mbox filter.
        $parts = explode('@', $field);
        if (count($parts) > 1) {
            return [
                ['$or' => [
                    'data->context->instructor->mbox' => 'mailto:' . $field,
                    'data->context->team->mbox' => 'mailto:' . $field,
                ]],
            ];
        }

        return [];
    }

    /**
     * Get context agent filter.
     *
     * @param  string  $field
     * @param  string  $relation
     * @return array
     */
    protected function getMagicContextActivityFilter($field, $relation)
    {
        if (\Str::startsWith($field, $relation.':')) {
            $field = \Str::after($field, $relation.':');
            if (empty($field)) {
                return [];
            }
            return [
                ['data->context->contextActivities->'.$relation.'[*]->id' => $field],
            ];
        }
    }

    /**
     * Get profile filter.
     *
     * @param  string  $field
     * @return array
     */
    protected function getMagicContextProfileFilter($field)
    {
        if (\Str::startsWith($field, 'profile:')) {
            $field = \Str::after($field, 'profile:');
            if (empty($field)) {
                return [];
            }
            return [
                ['data->context->contextActivities->category[*]->id' => ['$text' => $field]],
            ];
        }
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
     * Get activity name.
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
     * Get agent mbox.
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
     * Get HTTP field.
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
