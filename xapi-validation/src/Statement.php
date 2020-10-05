<?php

namespace Trax\XapiValidation;

use Trax\XapiValidation\Contracts\Validator;
use Trax\XapiValidation\Contracts\Comparator;
use Trax\XapiValidation\Contracts\Formatter;
use Trax\XapiValidation\Traits\IsValid;
use Trax\XapiValidation\Traits\CompareStatements;
use Trax\XapiValidation\Traits\FormatStatements;
use Trax\XapiValidation\Parsing\StatementSchema;
use Trax\XapiValidation\Parsing\Parser;
use Trax\XapiValidation\Exceptions\XapiValidationException;

class Statement implements Validator, Comparator, Formatter
{
    use IsValid, CompareStatements, FormatStatements;

    /**
     * Validate a statement and return a list of errors.
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
        $errors = $parser->validate($data, 'statement');
        if (!empty($errors)) {
            throw new XapiValidationException('This statement is not valid: ', $data, $errors);
        }
    }
}
