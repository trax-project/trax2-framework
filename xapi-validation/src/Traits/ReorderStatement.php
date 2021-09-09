<?php

namespace Trax\XapiValidation\Traits;

trait ReorderStatement
{
    /**
     * Reorder the statement props.
     *
     * @param  object  $object
     * @return object
     */
    public static function reorderStatement($object)
    {
        $reordered = self::reorder($object);
        $reordered->actor = self::reorderActor($reordered->actor);
        $reordered->verb = self::reorder($reordered->verb);
        $reordered->object = self::reorderObject($reordered->object);
        if (isset($reordered->result)) {
            $reordered->result = self::reorder($reordered->result);
        }
        if (isset($reordered->context)) {
            $reordered->context = self::reorderContext($reordered->context);
        }
        if (isset($reordered->authority)) {
            $reordered->authority = self::reorderActor($reordered->authority);
        }
        return $reordered;
    }

    /**
     * Reorder the actor props.
     *
     * @param  object  $object
     * @return object
     */
    protected static function reorderActor($object)
    {
        $reordered = self::reorder($object);
        if (isset($reordered->account)) {
            $reordered->account = self::reorder($reordered->account);
        }
        if (isset($reordered->member)) {
            $reordered->member = array_map(function ($item) {
                return self::reorderActor($item);
            }, $reordered->member);
        }
        return $reordered;
    }

    /**
     * Reorder the object props.
     *
     * @param  object  $object
     * @return object
     */
    protected static function reorderObject($object)
    {
        if (!isset($object->objectType)) {
            return self::reorderActivity($object);
        }
        switch ($object->objectType) {
            case 'Agent':
            case 'Group':
                return self::reorderActor($object);
            case 'SubStatement':
                return self::reorderStatement($object);
            case 'StatementRef':
                return self::reorder($object);
            default:
                return self::reorderActivity($object);
        }
    }

    /**
     * Reorder the activity props.
     *
     * @param  object  $object
     * @return object
     */
    protected static function reorderActivity($object)
    {
        $reordered = self::reorder($object);
        if (isset($reordered->definition)) {
            $reordered->definition = self::reorder($reordered->definition);
        }
        return $reordered;
    }

    /**
     * Reorder the context props.
     *
     * @param  object  $object
     * @return object
     */
    protected static function reorderContext($object)
    {
        $reordered = self::reorder($object);
        if (isset($reordered->instructor)) {
            $reordered->instructor = self::reorderActor($reordered->instructor);
        }
        if (isset($reordered->team)) {
            $reordered->team = self::reorderActor($reordered->team);
        }
        if (isset($reordered->contextActivities)) {
            $reordered->contextActivities = self::reorder($reordered->contextActivities);

            if (isset($reordered->contextActivities->parent)) {
                $reordered->contextActivities->parent = array_map(function ($item) {
                    return self::reorderActivity($item);
                }, $reordered->contextActivities->parent);
            }

            if (isset($reordered->contextActivities->grouping)) {
                $reordered->contextActivities->grouping = array_map(function ($item) {
                    return self::reorderActivity($item);
                }, $reordered->contextActivities->grouping);
            }

            if (isset($reordered->contextActivities->category)) {
                $reordered->contextActivities->category = array_map(function ($item) {
                    return self::reorderActivity($item);
                }, $reordered->contextActivities->category);
            }

            if (isset($reordered->contextActivities->other)) {
                $reordered->contextActivities->other = array_map(function ($item) {
                    return self::reorderActivity($item);
                }, $reordered->contextActivities->other);
            }
        }
        return $reordered;
    }

    /**
     * Reorder a given object props.
     *
     * @param  object  $object
     * @return object
     */
    protected static function reorder($object)
    {
        $props = [

            // Statement.
            'objectType', 'id', 'actor', 'verb', 'object', 'result', 'context',
            'timestamp', 'stored', 'authority', 'version', 'attachments',

            // Agent / Group.
            'name', 'mbox', 'mbox_sha1sum', 'openid', 'account', 'homePage', 'member',

            // Verb.
            'display',

            // Activity.
            'definition', 'description', 'type', 'moreInfo', 'interactionType',
            'correctResponsesPattern', 'choices', 'scale', 'source', 'target', 'steps',

            // Result.
            'score', 'success', 'completion', 'response', 'duration',
            
            // Context.
            'registration', 'instructor', 'team', 'contextActivities', 'revision',
            'platform', 'language', 'statement',
            'parent', 'grouping', 'category', 'other',

            // Activities, result, context
            'extensions'
        ];
        $reordered = (object)[];
        foreach ($props as $prop) {
            if (isset($object->$prop)) {
                $reordered->$prop = $object->$prop;
            }
        }
        return $reordered;
    }
}
