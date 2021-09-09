<?php

namespace Trax\XapiValidation;

use Trax\XapiValidation\Contracts\Validator;
use Trax\XapiValidation\Traits\ValidateStatements;
use Trax\XapiValidation\Traits\CompareStatements;
use Trax\XapiValidation\Traits\FormatStatements;

class Statement implements Validator
{
    use ValidateStatements, CompareStatements, FormatStatements;
}
