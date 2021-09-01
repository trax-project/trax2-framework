<?php

namespace Trax\XapiValidation\Parsing;

use Trax\Core\Validation;
use Trax\XapiValidation\Contracts\Schema;

class StatementSchema implements Schema
{
    public $statement = [
        'id' => ['format' => 'uuid'],
        'stored' => ['format' => 'iso_date'],
        'authority' => ['$or' => [
            'agent',
            'authority_group',
        ]],
        'version' => ['format' => 'xapi_version'],
        '$extend' => ['type' => 'statement_core'],
    ];
    
    public $statement_core = [
        'actor' => ['required', '$or' => [
            'agent',
            'anonymous_group',
            'identified_group',
        ]],
        'verb' => ['required', 'format' => 'object'],
        'object' => ['required', '$or' => [
            'agent',
            'anonymous_group',
            'identified_group',
            'activity',
            'statement_ref',
            'sub_statement',
        ]],
        'result' => ['format' => 'object'],
        'context' => ['format' => 'object'],
        'timestamp' => ['format' => 'iso_date'],
        'attachments' => ['format' => 'array', 'items' => 'object', 'type' => 'attachment'],
    ];
    
    public $agent = [
        'objectType' => ['format' => 'string', 'value' => 'Agent'],
        'name' => ['format' => 'string', 'descriptive'],
        '$extend' => ['type' => 'inverse_functional_identifier', 'required'],
    ];
    
    public $anonymous_group = [
        'objectType' => ['format' => 'string', 'value' => 'Group', 'required'],
        'name' => ['format' => 'string', 'descriptive'],
        'member' => ['format' => 'array', 'items' => 'object', 'type' => 'agent', 'required'],
    ];
    
    public $identified_group = [
        'objectType' => ['format' => 'string', 'value' => 'Group', 'required'],
        'name' => ['format' => 'string', 'descriptive'],
        'member' => ['format' => 'array', 'items' => 'object', 'type' => 'agent'],
        '$extend' => ['type' => 'inverse_functional_identifier', 'required'],
    ];
    
    public $authority_group = [
        'objectType' => ['format' => 'string', 'value' => 'Group', 'required'],
        'member' => ['format' => 'array', 'items' => 'object', 'type' => 'agent', 'required'],
    ];
    
    public $inverse_functional_identifier = [
        '$choice' => [
            'mbox' => ['format' => 'xapi_mbox'],
            'openid' => ['format' => 'url'],
            'mbox_sha1sum' => ['format' => 'string'],  // Dont move this. String type is more generic than previous.
            'account' => ['format' => 'object'],
        ]
    ];
    
    public $account = [
        'homePage' => ['format' => 'url', 'required'],
        'name' => ['format' => 'string', 'required'],
    ];
    
    public $verb = [
        'id' => ['format' => 'iri', 'required'],
        'display' => ['format' => 'xapi_lang_map', 'descriptive'],
    ];
    
    public $activity = [
        'objectType' => ['format' => 'string', 'value' => 'Activity'],
        'id' => ['format' => 'iri', 'required'],
        'definition' => ['descriptive', '$or' => [
                'definition',
                'interaction_definition',
            ]],
        ];
    
    public $definition = [
        'name' => ['format' => 'xapi_lang_map'],
        'description' => ['format' => 'xapi_lang_map'],
        'type' => ['format' => 'iri'],
        'moreInfo' => ['format' => 'url'],
        'extensions' => ['format' => 'object'],
    ];
    
    public $interaction_definition = [
        'name' => ['format' => 'xapi_lang_map'],
        'description' => ['format' => 'xapi_lang_map'],
        'type' => ['format' => 'iri'],
        'moreInfo' => ['format' => 'url'],
        'extensions' => ['format' => 'object'],
        '$extend' => ['$or' => [
                'interaction_true_false',
                'interaction_choice',
                'interaction_fill_in',
                'interaction_long_fill_in',
                'interaction_matching',
                'interaction_performance',
                'interaction_sequencing',
                'interaction_likert',
                'interaction_numeric',
                'interaction_other',
            ]],
        ];
    
    public $interaction_true_false = [
        'interactionType' => ['format' => 'string', 'value' => 'true-false', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
    ];
    
    public $interaction_choice = [
        'interactionType' => ['format' => 'string', 'value' => 'choice', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
        'choices' => ['format' => 'array', 'items' => 'object', 'type' => 'interaction_component'],
    ];
    
    public $interaction_fill_in = [
        'interactionType' => ['format' => 'string', 'value' => 'fill-in', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
    ];
    
    public $interaction_long_fill_in = [
        'interactionType' => ['format' => 'string', 'value' => 'long-fill-in', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
    ];
    
    public $interaction_matching = [
        'interactionType' => ['format' => 'string', 'value' => 'matching', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
        'source' => ['format' => 'array', 'items' => 'object', 'type' => 'interaction_component'],
        'target' => ['format' => 'array', 'items' => 'object', 'type' => 'interaction_component'],
    ];
    
    public $interaction_performance = [
        'interactionType' => ['format' => 'string', 'value' => 'performance', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
        'steps' => ['format' => 'array', 'items' => 'object', 'type' => 'interaction_component'],
    ];
    
    public $interaction_sequencing = [
        'interactionType' => ['format' => 'string', 'value' => 'sequencing', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
        'choices' => ['format' => 'array', 'items' => 'object', 'type' => 'interaction_component'],
    ];
    
    public $interaction_likert = [
        'interactionType' => ['format' => 'string', 'value' => 'likert', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
        'scale' => ['format' => 'array', 'items' => 'object', 'type' => 'interaction_component'],
    ];
    
    public $interaction_numeric = [
        'interactionType' => ['format' => 'string', 'value' => 'numeric', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
    ];
    
    public $interaction_other = [
        'interactionType' => ['format' => 'string', 'value' => 'other', 'required'],
        'correctResponsesPattern' => ['format' => 'array', 'items' => 'string'],
    ];
    
    public $interaction_component = [
        'id' => ['format' => 'string', 'required'],
        'description' => ['format' => 'xapi_lang_map'],
    ];
    
    public $statement_ref = [
        'objectType' => ['format' => 'string', 'value' => 'StatementRef', 'required'],
        'id' => ['format' => 'uuid', 'required'],
    ];
    
    public $sub_statement = [
        'objectType' => ['format' => 'string', 'value' => 'SubStatement', 'required'],
        '$extend' => ['type' => 'statement_core'],
    ];
    
    public $result = [
        'score' => ['format' => 'object'],
        'success' => ['format' => 'boolean'],
        'completion' => ['format' => 'boolean'],
        'response' => ['format' => 'string'],
        'duration' => ['format' => 'iso_duration'],
        'extensions' => ['format' => 'object'],
    ];
    
    public $score = [
        'scaled' => ['format' => 'xapi_scaled'],
        'raw' => ['format' => 'strict_numeric'],
        'min' => ['format' => 'strict_numeric'],
        'max' => ['format' => 'strict_numeric'],
    ];
    
    public $context = [
        'registration' => ['format' => 'uuid'],
        'instructor' => ['$or' => [
            'agent',
            'anonymous_group',
            'identified_group',
        ]],
        'team' => ['$or' => [
            'anonymous_group',
            'identified_group',
        ]],
        'contextActivities' => ['format' => 'object', 'type' => 'context_activities'],
        'revision' => ['format' => 'string'],
        'platform' => ['format' => 'string'],
        'language' => ['format' => 'iso_lang'],
        'statement' => ['format' => 'object', 'type' => 'statement_ref'],
        'extensions' => ['format' => 'object'],
    ];
    
    public $context_activities = [
        'parent' => ['$or' => [
            'activity',
            ['activity'],
        ]],
        'grouping' => ['$or' => [
            'activity',
            ['activity'],
        ]],
        'category' => ['$or' => [
            'activity',
            ['activity'],
        ]],
        'other' => ['$or' => [
            'activity',
            ['activity'],
        ]],
    ];
    
    public $attachment = [
        'usageType' => ['format' => 'iri', 'required'],
        'display' => ['format' => 'xapi_lang_map', 'required'],
        'description' => ['format' => 'xapi_lang_map'],
        'contentType' => ['format' => 'content_type', 'required'],
        'length' => ['format' => 'strict_integer|min:0', 'required'],
        'sha2' => ['format' => 'string', 'required'],
        'fileUrl' => ['format' => 'url'],
    ];
    
    public function statement($object, string $path = ''): array
    {
        $errors = [];

        if (isset($object->verb) && isset($object->verb->id) && $object->verb->id == 'http://adlnet.gov/expapi/verbs/voided'
            && (!isset($object->object) || !isset($object->object->objectType) || $object->object->objectType != 'StatementRef')) {
            $errors[] = ['object.objectType' => 'The object of a voiding statement must have a StatementRef type.'];
        }
        
        if (!isset($object->object->objectType)
            && (isset($object->object->mbox) || isset($object->object->openid) || isset($object->object->mbox_sha1sum) || isset($object->object->account))) {
            $errors[] = ['object.objectType' => 'The objectType must be Agent or Group.'];
        }
        
        if (isset($object->context->revision) && isset($object->object->objectType) && $object->object->objectType != 'Activity') {
            $errors[] = ['object.context.revision' => 'The revision property can be defined only when the object is an activity.'];
        }
        
        if (isset($object->context->platform) && isset($object->object->objectType) && $object->object->objectType != 'Activity') {
            $errors[] = ['object.context.platform' => 'The platform property can be defined only when the object is an activity.'];
        }

        return $errors;
    }

    public function subStatement($object, string $path = ''): array
    {
        $errors = [];

        if (isset($object->object->objectType) && $object->object->objectType == 'SubStatement') {
            $errors[] = ['object.object.objectType' => 'Nested sub-statements are not allowed.'];
        }
        
        if (isset($object->context->revision) && isset($object->object->objectType) && $object->object->objectType != 'Activity') {
            $errors[] = ['object.object.context.revision' => 'The revision property can be defined only when the object is an activity.'];
        }
        
        if (isset($object->context->platform) && isset($object->object->objectType) && $object->object->objectType != 'Activity') {
            $errors[] = ['object.object.context.platform' => 'The platform property can be defined only when the object is an activity.'];
        }

        return $errors;
    }
    
    public function score($object, string $path = ''): array
    {
        $errors = [];

        if (isset($object->raw) && isset($object->min) && $object->raw < $object->min) {
            $errors[] = ["$path.raw" => 'Raw score must be greater than min score.'];
        }
        
        if (isset($object->raw) && isset($object->max) && $object->raw > $object->max) {
            $errors[] = ["$path.raw" => 'Raw score must be lower than max score.'];
        }

        return $errors;
    }

    public function authorityGroup($object, string $path = ''): array
    {
        $errors = [];

        if (!isset($object->member)) {
            return [[$path => 'The authority members are not defined.']];
        }

        if (!is_array($object->member)) {
            return [[$path => 'The authority members must be an array.']];
        }

        if (count($object->member) != 2) {
            $errors[] = ["$path.member" => 'The authority must have 2 members.'];
        }

        return $errors;
    }
    
    public function extensions($object, string $path = ''): array
    {
        $errors = [];
        $props = get_object_vars($object);
        foreach ($props as $key => $val) {
            //
            if (Validation::check($key, 'iri')) {
                continue;
            }

            $words = explode(' ', $key);
            if (count($words) > 1) {
                $errors[] = ["$path.$key" => 'The extension key must not contain spaces.'];
            }

            $parts = explode(':', $key);
            if (count($parts) == 1) {
                $errors[] = ["$path.$key" => 'The extension key must include the [:] character.'];
            }
        }
        return $errors;
    }
}
