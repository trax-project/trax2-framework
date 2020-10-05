<?php

namespace Trax\XapiValidation;

use Trax\XapiValidation\Contracts\Validator;
use Trax\XapiValidation\Traits\IsValid;
use Trax\XapiValidation\Parsing\StatementSchema;
use Trax\XapiValidation\Parsing\Parser;
use Trax\XapiValidation\Exceptions\XapiValidationException;

class Agent implements Validator
{
    use IsValid;
    
    /**
     * Validate an agent and return a list of errors.
     *
     * @param  mixed  $data
     * @return array
     *
     * @throws \Trax\XapiValidation\Exceptions\XapiValidationException
     */
    public static function validate($data)
    {
        $schema = new StatementSchema();
        $parser = new Parser($schema);
        $errors = $parser->validate($data, 'agent');
        if (!empty($errors)) {
            throw new XapiValidationException('This agent is not valid.', $data, $errors);
        }
    }
}
